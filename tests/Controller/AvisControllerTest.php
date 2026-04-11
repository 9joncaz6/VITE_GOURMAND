<?php

namespace App\Tests\Controller;

use App\Entity\Avis;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AvisControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Avis> */
    private EntityRepository $aviRepository;
    private string $path = '/avis/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->aviRepository = $this->manager->getRepository(Avis::class);

        foreach ($this->aviRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Avi index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'avi[note]' => 'Testing',
            'avi[commentaire]' => 'Testing',
            'avi[valide]' => 'Testing',
            'avi[utilisateur]' => 'Testing',
            'avi[commande]' => 'Testing',
        ]);

        self::assertResponseRedirects('/avis');

        self::assertSame(1, $this->aviRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Avis();
        $fixture->setNote('My Title');
        $fixture->setCommentaire('My Title');
        $fixture->setValide('My Title');
        $fixture->setUtilisateur('My Title');
        $fixture->setCommande('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Avi');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Avis();
        $fixture->setNote('Value');
        $fixture->setCommentaire('Value');
        $fixture->setValide('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setCommande('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'avi[note]' => 'Something New',
            'avi[commentaire]' => 'Something New',
            'avi[valide]' => 'Something New',
            'avi[utilisateur]' => 'Something New',
            'avi[commande]' => 'Something New',
        ]);

        self::assertResponseRedirects('/avis');

        $fixture = $this->aviRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNote());
        self::assertSame('Something New', $fixture[0]->getCommentaire());
        self::assertSame('Something New', $fixture[0]->getValide());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());
        self::assertSame('Something New', $fixture[0]->getCommande());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Avis();
        $fixture->setNote('Value');
        $fixture->setCommentaire('Value');
        $fixture->setValide('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setCommande('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/avis');
        self::assertSame(0, $this->aviRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
