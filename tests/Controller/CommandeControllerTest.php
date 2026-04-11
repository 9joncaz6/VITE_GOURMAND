<?php

namespace App\Tests\Controller;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CommandeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Commande> */
    private EntityRepository $commandeRepository;
    private string $path = '/commande/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->commandeRepository = $this->manager->getRepository(Commande::class);

        foreach ($this->commandeRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Commande index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'commande[dateCommande]' => 'Testing',
            'commande[datePrestation]' => 'Testing',
            'commande[heurePrestation]' => 'Testing',
            'commande[adresseLivraison]' => 'Testing',
            'commande[villeLivraison]' => 'Testing',
            'commande[distanceKm]' => 'Testing',
            'commande[prixLivraison]' => 'Testing',
            'commande[nbPersonnes]' => 'Testing',
            'commande[prixTotal]' => 'Testing',
            'commande[statutActuel]' => 'Testing',
            'commande[utilisateur]' => 'Testing',
            'commande[menu]' => 'Testing',
            'commande[avis]' => 'Testing',
        ]);

        self::assertResponseRedirects('/commande');

        self::assertSame(1, $this->commandeRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Commande();
        $fixture->setDateCommande('My Title');
        $fixture->setDatePrestation('My Title');
        $fixture->setHeurePrestation('My Title');
        $fixture->setAdresseLivraison('My Title');
        $fixture->setVilleLivraison('My Title');
        $fixture->setDistanceKm('My Title');
        $fixture->setPrixLivraison('My Title');
        $fixture->setNbPersonnes('My Title');
        $fixture->setPrixTotal('My Title');
        $fixture->setStatutActuel('My Title');
        $fixture->setUtilisateur('My Title');
        $fixture->setMenu('My Title');
        $fixture->setAvis('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Commande');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Commande();
        $fixture->setDateCommande('Value');
        $fixture->setDatePrestation('Value');
        $fixture->setHeurePrestation('Value');
        $fixture->setAdresseLivraison('Value');
        $fixture->setVilleLivraison('Value');
        $fixture->setDistanceKm('Value');
        $fixture->setPrixLivraison('Value');
        $fixture->setNbPersonnes('Value');
        $fixture->setPrixTotal('Value');
        $fixture->setStatutActuel('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setMenu('Value');
        $fixture->setAvis('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'commande[dateCommande]' => 'Something New',
            'commande[datePrestation]' => 'Something New',
            'commande[heurePrestation]' => 'Something New',
            'commande[adresseLivraison]' => 'Something New',
            'commande[villeLivraison]' => 'Something New',
            'commande[distanceKm]' => 'Something New',
            'commande[prixLivraison]' => 'Something New',
            'commande[nbPersonnes]' => 'Something New',
            'commande[prixTotal]' => 'Something New',
            'commande[statutActuel]' => 'Something New',
            'commande[utilisateur]' => 'Something New',
            'commande[menu]' => 'Something New',
            'commande[avis]' => 'Something New',
        ]);

        self::assertResponseRedirects('/commande');

        $fixture = $this->commandeRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDateCommande());
        self::assertSame('Something New', $fixture[0]->getDatePrestation());
        self::assertSame('Something New', $fixture[0]->getHeurePrestation());
        self::assertSame('Something New', $fixture[0]->getAdresseLivraison());
        self::assertSame('Something New', $fixture[0]->getVilleLivraison());
        self::assertSame('Something New', $fixture[0]->getDistanceKm());
        self::assertSame('Something New', $fixture[0]->getPrixLivraison());
        self::assertSame('Something New', $fixture[0]->getNbPersonnes());
        self::assertSame('Something New', $fixture[0]->getPrixTotal());
        self::assertSame('Something New', $fixture[0]->getStatutActuel());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());
        self::assertSame('Something New', $fixture[0]->getMenu());
        self::assertSame('Something New', $fixture[0]->getAvis());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Commande();
        $fixture->setDateCommande('Value');
        $fixture->setDatePrestation('Value');
        $fixture->setHeurePrestation('Value');
        $fixture->setAdresseLivraison('Value');
        $fixture->setVilleLivraison('Value');
        $fixture->setDistanceKm('Value');
        $fixture->setPrixLivraison('Value');
        $fixture->setNbPersonnes('Value');
        $fixture->setPrixTotal('Value');
        $fixture->setStatutActuel('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setMenu('Value');
        $fixture->setAvis('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/commande');
        self::assertSame(0, $this->commandeRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
