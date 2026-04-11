<?php

namespace App\Tests\Controller;

use App\Entity\CommandeStatut;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CommandeStatutControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<CommandeStatut> */
    private EntityRepository $commandeStatutRepository;
    private string $path = '/commande/statut/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->commandeStatutRepository = $this->manager->getRepository(CommandeStatut::class);

        foreach ($this->commandeStatutRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CommandeStatut index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'commande_statut[statut]' => 'Testing',
            'commande_statut[dateMaj]' => 'Testing',
            'commande_statut[commentaire]' => 'Testing',
            'commande_statut[commande]' => 'Testing',
        ]);

        self::assertResponseRedirects('/commande/statut');

        self::assertSame(1, $this->commandeStatutRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new CommandeStatut();
        $fixture->setStatut('My Title');
        $fixture->setDateMaj('My Title');
        $fixture->setCommentaire('My Title');
        $fixture->setCommande('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CommandeStatut');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new CommandeStatut();
        $fixture->setStatut('Value');
        $fixture->setDateMaj('Value');
        $fixture->setCommentaire('Value');
        $fixture->setCommande('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'commande_statut[statut]' => 'Something New',
            'commande_statut[dateMaj]' => 'Something New',
            'commande_statut[commentaire]' => 'Something New',
            'commande_statut[commande]' => 'Something New',
        ]);

        self::assertResponseRedirects('/commande/statut');

        $fixture = $this->commandeStatutRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getStatut());
        self::assertSame('Something New', $fixture[0]->getDateMaj());
        self::assertSame('Something New', $fixture[0]->getCommentaire());
        self::assertSame('Something New', $fixture[0]->getCommande());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new CommandeStatut();
        $fixture->setStatut('Value');
        $fixture->setDateMaj('Value');
        $fixture->setCommentaire('Value');
        $fixture->setCommande('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/commande/statut');
        self::assertSame(0, $this->commandeStatutRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
