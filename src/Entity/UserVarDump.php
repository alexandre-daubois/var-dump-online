<?php


namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserVarDump
 * @package App\Entity
 *
 * @ORM\Entity()
 * @ORM\Table()
 */
class UserVarDump
{
    /**
     * @var
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @Encrypted()
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @var ?\DateTime
     * @ORM\Column(type="datetime")
     */
    protected $submittedAt;

    /**
     * @var
     * @ORM\Column(type="text")
     */
    protected $token;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

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
     * @return \DateTime|null
     */
    public function getSubmittedAt(): ?\DateTime
    {
        return $this->submittedAt;
    }

    /**
     * @param \DateTime|null $submittedAt
     */
    public function setSubmittedAt(?\DateTime $submittedAt): void
    {
        $this->submittedAt = $submittedAt;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }
}