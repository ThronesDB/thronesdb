<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Append-only audit trail for card review edits.
 *
 * @ORM\Table(name="review_edit_history")
 * @ORM\Entity
 */
class ReviewEditHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(name="review_id", type="integer") */
    private $reviewId;

    /** @ORM\Column(name="author_user_id", type="integer") */
    private $authorUserId;

    /** @ORM\Column(name="editor_user_id", type="integer") */
    private $editorUserId;

    /** @ORM\Column(name="edited_at", type="datetime") */
    private $editedAt;

    /** @ORM\Column(name="previous_text_md", type="text") */
    private $previousTextMd;

    /** @ORM\Column(name="new_text_md", type="text") */
    private $newTextMd;

    public function __construct(
        ReviewInterface $review,
        UserInterface $editor,
        string $previousText,
        string $newText
    ) {
        $this->reviewId = (int) $review->getId();
        $this->authorUserId = (int) $review->getUser()->getId();
        $this->editorUserId = (int) $editor->getId();
        $this->editedAt = new DateTime();
        $this->previousTextMd = $previousText;
        $this->newTextMd = $newText;
    }
}
