<?php

namespace App\Tests\Controller;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MenuControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Menu> */
    private EntityRepository $menuRepository;
    private string $path = '/menu/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->menuRepository = $this->manager->getRepository(Menu::class);

        foreach ($this->menuRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Menu index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'menu[titre]' => 'Testing',
            'menu[description]' => 'Testing',
            'menu[nbPersonnesMin]' => 'Testing',
            'menu[prixBase]' => 'Testing',
            'menu[conditions]' => 'Testing',
            'menu[stockDisponible]' => 'Testing',
            'menu[theme]' => 'Testing',
            'menu[regime]' => 'Testing',
            'menu[plats]' => 'Testing',
        ]);

        self::assertResponseRedirects('/menu');

        self::assertSame(1, $this->menuRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Menu();
        $fixture->setTitre('My Title');
        $fixture->setDescription('My Title');
        $fixture->setNbPersonnesMin('My Title');
        $fixture->setPrixBase('My Title');
        $fixture->setConditions('My Title');
        $fixture->setStockDisponible('My Title');
        $fixture->setTheme('My Title');
        $fixture->setRegime('My Title');
        $fixture->setPlats('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Menu');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Menu();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setNbPersonnesMin('Value');
        $fixture->setPrixBase('Value');
        $fixture->setConditions('Value');
        $fixture->setStockDisponible('Value');
        $fixture->setTheme('Value');
        $fixture->setRegime('Value');
        $fixture->setPlats('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'menu[titre]' => 'Something New',
            'menu[description]' => 'Something New',
            'menu[nbPersonnesMin]' => 'Something New',
            'menu[prixBase]' => 'Something New',
            'menu[conditions]' => 'Something New',
            'menu[stockDisponible]' => 'Something New',
            'menu[theme]' => 'Something New',
            'menu[regime]' => 'Something New',
            'menu[plats]' => 'Something New',
        ]);

        self::assertResponseRedirects('/menu');

        $fixture = $this->menuRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getNbPersonnesMin());
        self::assertSame('Something New', $fixture[0]->getPrixBase());
        self::assertSame('Something New', $fixture[0]->getConditions());
        self::assertSame('Something New', $fixture[0]->getStockDisponible());
        self::assertSame('Something New', $fixture[0]->getTheme());
        self::assertSame('Something New', $fixture[0]->getRegime());
        self::assertSame('Something New', $fixture[0]->getPlats());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Menu();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setNbPersonnesMin('Value');
        $fixture->setPrixBase('Value');
        $fixture->setConditions('Value');
        $fixture->setStockDisponible('Value');
        $fixture->setTheme('Value');
        $fixture->setRegime('Value');
        $fixture->setPlats('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/menu');
        self::assertSame(0, $this->menuRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
