<?php

namespace App\Tests\Controller;

use App\Entity\Plat;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PlatControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Plat> */
    private EntityRepository $platRepository;
    private string $path = '/plat/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->platRepository = $this->manager->getRepository(Plat::class);

        foreach ($this->platRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Plat index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'plat[nom]' => 'Testing',
            'plat[description]' => 'Testing',
            'plat[type]' => 'Testing',
            'plat[allergenes]' => 'Testing',
            'plat[menus]' => 'Testing',
        ]);

        self::assertResponseRedirects('/plat');

        self::assertSame(1, $this->platRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Plat();
        $fixture->setNom('My Title');
        $fixture->setDescription('My Title');
        $fixture->setType('My Title');
        $fixture->setAllergenes('My Title');
        $fixture->setMenus('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Plat');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Plat();
        $fixture->setNom('Value');
        $fixture->setDescription('Value');
        $fixture->setType('Value');
        $fixture->setAllergenes('Value');
        $fixture->setMenus('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'plat[nom]' => 'Something New',
            'plat[description]' => 'Something New',
            'plat[type]' => 'Something New',
            'plat[allergenes]' => 'Something New',
            'plat[menus]' => 'Something New',
        ]);

        self::assertResponseRedirects('/plat');

        $fixture = $this->platRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getType());
        self::assertSame('Something New', $fixture[0]->getAllergenes());
        self::assertSame('Something New', $fixture[0]->getMenus());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Plat();
        $fixture->setNom('Value');
        $fixture->setDescription('Value');
        $fixture->setType('Value');
        $fixture->setAllergenes('Value');
        $fixture->setMenus('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/plat');
        self::assertSame(0, $this->platRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
