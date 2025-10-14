<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Repository\RequestPasswordRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RequestPasswordRepository::class)]
class RequestPassword
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expireAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct(int $lifetimeInSeconds)
    {
        $this->token = Uuid::v4();
        $this->expireAt = (new \DateTimeImmutable())->modify('+'.$lifetimeInSeconds.' seconds');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?Uuid
    {
        return $this->token;
    }

    public function setToken(Uuid $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpireAt(): ?\DateTimeImmutable
    {
        return $this->expireAt;
    }

    public function setExpireAt(\DateTimeImmutable $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }
}
