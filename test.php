<?php

use TreptowKolleg\ORM\Model\EntityManager;
use TreptowKolleg\ORM\Model\Repository;
use TreptowKolleg\ORM\ORM;

require_once 'vendor/autoload.php';

class TeacherRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Teacher::class);
    }

    public function search(string $term): string
    {
        return "";
    }
}

class Teacher
{

    #[ORM\Id]
    #[ORM\AutoGenerated]
    #[ORM\Column(type: ORM\Types::Integer)]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private string $firstname;

    #[ORM\Column(length: 50)]
    private string $lastname;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

}

class EmployeeList
{

    #[ORM\Id]
    #[ORM\AutoGenerated]
    #[ORM\Column(type: ORM\Types::Integer)]
    private ?int $id;

    #[ORM\Column(type: ORM\Types::ManyToOne)]
    #[ORM\ManyToOne(Teacher::class, "id")]
    private int $teacher;
    private Teacher $teacherObject;

    #[ORM\Column(type: ORM\Types::Date)]
    private string $startDate;

    #[ORM\Column(type: ORM\Types::Date, nullable: true)]
    private ?string $endDate = null;

    public function __construct()
    {
        if(isset($this->teacher)){
            $this->teacherObject = (new Repository(Teacher::class))->find($this->teacher);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeacher(): Teacher
    {
        return $this->teacherObject;
    }

    public function setTeacher(Teacher $teacher): void
    {
        $this->teacherObject = $teacher;
        $this->teacher = $teacher->getId();
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat("Y-m-d",$this->startDate);
    }

    public function setStartDate(\DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate->format("Y-m-d");
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat("Y-m-d",$this->endDate);
    }

    public function setEndDate(\DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate->format("Y-m-d");
    }

}

$tr = new TeacherRepository();
$a = $tr->search("Haööp");
$t = $tr->find(1);

echo $t->getFirstname();

$em = new EntityManager();
$em->createTable(EmployeeList::class);

$pupil = new Teacher();
$pupil->setFirstname('John');
$pupil->setLastname('Doe');

$ben = Repository::new(Teacher::class)->findOneBy(['firstname' => 'Benjamin']);
echo $ben?->getFirstname() ?: "Kein Datensatz gefunden";
if($ben) {
    $ben->setLastname('Voigt');
    $em->persist($ben);
    $list = new EmployeeList();
    $list->setTeacher($ben);
    $list->setStartDate(DateTimeImmutable::createFromMutable(new DateTime("now")));
    $em->persist($list);
}

$myList = (new Repository(EmployeeList::class))->findOneBy(['teacher' => $ben->getId()]);

print_r($myList);


$em->persist($pupil);
$em->flush();

