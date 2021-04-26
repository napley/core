<?php

declare(strict_types=1);

namespace Bolt\Entity;

use Bolt\Common\Json;
use Bolt\Enum\UserStatus;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Bolt\Repository\UserRepository")
 * @UniqueEntity("email", message="user.duplicate_email")
 * @UniqueEntity("username", message="user.duplicate_username")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("get_user")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank(normalizer="trim", message="user.not_valid_display_name")
     * @Assert\Length(min=2, max=50, minMessage="user.not_valid_display_name")
     * @Groups({"get_content", "get_user"})
     */
    private $displayName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=191)
     * @Assert\NotBlank(normalizer="trim")
     * @Assert\Length(min=2, max=50)
     * @Assert\Regex(pattern="/^[a-z0-9_]+$/", message="user.username_invalid_characters")
     * @Groups("get_user")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=191)
     * @Assert\NotBlank(normalizer="trim")
     * @Assert\Email(message="user.not_valid_email")
     * @Groups("get_user")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=191)
     */
    private $password;

    /**
     * @var string|null
     * @Assert\Length(min="6", minMessage="user.not_valid_password")
     */
    private $plainPassword;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     * @Groups("get_user")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("get_user")
     */
    private $lastseenAt;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    private $lastIp;

    /**
     * @ORM\Column(type="string", length=191, nullable=true)
     * @Groups("get_user")
     */
    private $locale;

    /** @ORM\Column(type="string", length=191, nullable=true) */
    private $backendTheme;

    /** @ORM\Column(type="string", length=30, options={"default":"enabled"}) */
    private $status = UserStatus::ENABLED;

    /** @ORM\OneToOne(targetEntity="Bolt\Entity\UserAuthToken", mappedBy="user", cascade={"persist", "remove"}) */
    private $userAuthToken;

    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function __toString()
    {
        return $this->getdisplayName();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $slugify = new Slugify(['separator' => '_']);
        $cleanUsername = $slugify->slugify($username);
        $this->username = $cleanUsername;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize([$this->id, $this->username, $this->password]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function getLastseenAt(): ?\DateTimeInterface
    {
        return $this->lastseenAt;
    }

    public function setLastseenAt(\DateTimeInterface $lastseenAt): self
    {
        $this->lastseenAt = $lastseenAt;

        return $this;
    }

    public function getLastIp(): ?string
    {
        return $this->lastIp;
    }

    public function setLastIp(?string $lastIp): self
    {
        $this->lastIp = $lastIp;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = Json::findScalar($locale);

        return $this;
    }

    public function getBackendTheme(): ?string
    {
        return $this->backendTheme;
    }

    public function setBackendTheme(?string $backendTheme): self
    {
        $this->backendTheme = $backendTheme;

        return $this;
    }

    public function getUserAuthToken(): ?UserAuthToken
    {
        return $this->userAuthToken;
    }

    public function setUserAuthToken(?UserAuthToken $userAuthToken): self
    {
        $this->userAuthToken = $userAuthToken;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $userAuthToken === null ? null : $this;
        if ($userAuthToken->getUser() !== $newUser) {
            $userAuthToken->setUser($newUser);
        }

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
