<?php


namespace App\Entity;


class UserVarDumpModel
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var ?\DateTime
     */
    protected $submittedAt;

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime|null ?\DateTime
     */
    public function getSubmittedAt(): ?\DateTime
    {
        return $this->submittedAt;
    }

    /**
     * @param \DateTime $submittedAt
     */
    public function setSubmittedAt(\DateTime $submittedAt): void
    {
        $this->submittedAt = $submittedAt;
    }
}