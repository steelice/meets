<?php

namespace App\Entity;

use App\Repository\MeetingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MeetingRepository::class)
 */
class Meeting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="meetings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     */
    private $location;

    /**
     * @ORM\Column(type="datetime")
     */
    private $beginsAt;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * Кеш количества гостей
     * @ORM\Column(type="integer")
     */
    private $usersGoing = 0;

    /**
     * Кеш количества комментариев
     * @ORM\Column(type="integer")
     */
    private $totalComments = 0;

    /**
     * @ORM\OneToMany(targetEntity=MeetingVisitor::class, mappedBy="meeting", orphanRemoval=true)
     */
    private $meetingVisitors;

    /**
     * @ORM\OneToMany(targetEntity=MeetingComment::class, mappedBy="meeting", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mainPhoto;

    /**
     * @Assert\File(mimeTypes="image/*", mimeTypesMessage="Вы должны указать корректную картинку")
     */
    private $mainPhotoFile;

    /**
     * Хранит список файлов для галереи. По идее более правильно хранить их в связанной таблице, но для экономии времени и кода в данном проекте я допустил, что они могут храниться таким простым способом.
     * @ORM\Column(type="array")
     */
    private $galleryPhotos = [];

    /**
     */
    private array $galleryPhotoFiles = [];

    public function __construct()
    {
        $this->meetingVisitors = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getBeginsAt(): ?\DateTimeInterface
    {
        return $this->beginsAt;
    }

    public function setBeginsAt(\DateTimeInterface $beginsAt): self
    {
        $this->beginsAt = $beginsAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUsersGoing(): ?int
    {
        return $this->usersGoing;
    }

    public function setUsersGoing(int $usersGoing): self
    {
        $this->usersGoing = $usersGoing;

        return $this;
    }

    public function getTotalComments(): ?int
    {
        return $this->totalComments;
    }

    public function setTotalComments(int $totalComments): self
    {
        $this->totalComments = $totalComments;

        return $this;
    }

    /**
     * @return Collection|MeetingVisitor[]
     */
    public function getMeetingVisitors(): Collection
    {
        return $this->meetingVisitors;
    }

    public function addMeetingVisitor(MeetingVisitor $meetingVisitor): self
    {
        if (!$this->meetingVisitors->contains($meetingVisitor)) {
            $this->meetingVisitors[] = $meetingVisitor;
            $meetingVisitor->setMeeting($this);
        }

        return $this;
    }

    public function removeMeetingVisitor(MeetingVisitor $meetingVisitor): self
    {
        if ($this->meetingVisitors->removeElement($meetingVisitor)) {
            // set the owning side to null (unless already changed)
            if ($meetingVisitor->getMeeting() === $this) {
                $meetingVisitor->setMeeting(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MeetingComment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(MeetingComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setMeeting($this);
        }

        return $this;
    }

    public function removeComment(MeetingComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getMeeting() === $this) {
                $comment->setMeeting(null);
            }
        }

        return $this;
    }

    public function getMainPhoto(): ?string
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(string $mainPhoto): self
    {
        $this->mainPhoto = $mainPhoto;

        return $this;
    }

    public function getGalleryPhotos(): ?array
    {
        return $this->galleryPhotos;
    }

    public function addGalleryPhoto(string $photo)
    {
        $this->galleryPhotos[] = $photo;
    }

    public function setGalleryPhotos(array $galleryPhotos): self
    {
        $this->galleryPhotos = $galleryPhotos;

        return $this;
    }

    /**
     * @return UploadedFile|null
     */
    public function getMainPhotoFile(): ?UploadedFile
    {
        return $this->mainPhotoFile;
    }

    /**
     * @param UploadedFile|null $mainPhotoFile
     * @return Meeting
     */
    public function setMainPhotoFile(?UploadedFile $mainPhotoFile): Meeting
    {
        $this->mainPhotoFile = $mainPhotoFile;
        return $this;
    }

    /**
     * @return array
     */
    public function getGalleryPhotoFiles(): array
    {
        return $this->galleryPhotoFiles;
    }

    /**
     * @param array $galleryPhotoFiles
     */
    public function setGalleryPhotoFiles(array $galleryPhotoFiles): Meeting
    {
        $this->galleryPhotoFiles = $galleryPhotoFiles;
        return $this;
    }
}
