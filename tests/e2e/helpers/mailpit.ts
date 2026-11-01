import { expect } from '@playwright/test';
import { mailpitURL } from '../playwright.config';

type MessageSummary = { ID: string; Subject: string; To: { Address: string }[] };

/** Waits for the newest message sent to `address` and returns its plain-text body. */
export async function waitForMail(address: string, subjectPattern?: RegExp): Promise<{ subject: string; text: string }> {
  let found: MessageSummary | undefined;
  await expect
    .poll(
      async () => {
        const res = await fetch(`${mailpitURL}/api/v1/search?query=${encodeURIComponent(`to:"${address}"`)}`);
        const data = (await res.json()) as { messages: MessageSummary[] };
        found = data.messages.find((m) => !subjectPattern || subjectPattern.test(m.Subject));
        return !!found;
      },
      { message: `mail to ${address}`, timeout: 20_000 },
    )
    .toBe(true);
  const res = await fetch(`${mailpitURL}/api/v1/message/${found!.ID}`);
  const msg = (await res.json()) as { Subject: string; Text: string; HTML: string };
  return { subject: msg.Subject, text: msg.Text || msg.HTML };
}

/** The first URL in a message body whose path contains `pathPart`. */
export function findLink(body: string, pathPart: string): string {
  const url = body.match(/https?:\/\/[^\s"'<>]+/g)?.find((u) => u.includes(pathPart));
  if (!url) throw new Error(`no link containing ${pathPart} in:\n${body}`);
  return url;
}
