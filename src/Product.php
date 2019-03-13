<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="Product", uniqueConstraints={@ORM\UniqueConstraint(name="Nom", columns={"Nom"})})
 * @ORM\Entity
 */
class Product
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Nom", type="string", length=40, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", length=16777215, nullable=false)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="Prix", type="float", precision=10, scale=0, nullable=false)
     */
    private $prix;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Product
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set prix
     *
     * @param float $prix
     *
     * @return Product
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return float
     */
    public function getPrix()
    {
        return $this->prix;
    }
}
