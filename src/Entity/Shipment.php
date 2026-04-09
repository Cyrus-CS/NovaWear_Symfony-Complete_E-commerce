<?php

namespace App\Entity;

use App\Enum\ShipmentStatus;
use App\Repository\ShipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShipmentRepository::class)]
class Shipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'shipments')]
    private ?Order $orderId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $carrier = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $trackingNumber = null;

    #[ORM\Column(enumType: ShipmentStatus::class)]
    private ?ShipmentStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $shippedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?Order
    {
        return $this->orderId;
    }

    public function setOrderId(?Order $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    public function setCarrier(?string $carrier): static
    {
        $this->carrier = $carrier;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    public function getStatus(): ?ShipmentStatus
    {
        return $this->status;
    }

    public function setStatus(ShipmentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getShippedAt(): ?\DateTimeImmutable
    {
        return $this->shippedAt;
    }

    public function setShippedAt(\DateTimeImmutable $shippedAt): static
    {
        $this->shippedAt = $shippedAt;

        return $this;
    }
}
