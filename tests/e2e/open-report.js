// npm run report                 opens the newest run's HTML report
// npm run report -- <folder>     opens a specific one (a name under reports/)
// npm run reports:clean          deletes all but the newest 10
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

const dir = path.join(__dirname, 'reports');
const runs = fs.existsSync(dir) ? fs.readdirSync(dir).filter((d) => fs.statSync(path.join(dir, d)).isDirectory()).sort() : [];
const arg = process.argv[2];

if (arg === '--clean') {
  for (const old of runs.slice(0, -10)) fs.rmSync(path.join(dir, old), { recursive: true, force: true });
  console.log(`kept ${Math.min(runs.length, 10)} of ${runs.length} reports`);
  process.exit(0);
}

const pick = arg ?? runs.at(-1);
if (!pick) {
  console.error('No reports yet: run the tests first.');
  process.exit(1);
}
console.log(`Opening reports/${pick}  (${runs.length} runs kept; npm run reports:clean to trim)`);
const res = spawnSync('npx', ['playwright', 'show-report', path.join(dir, pick)], { stdio: 'inherit', shell: true });
process.exit(res.status ?? 0);
