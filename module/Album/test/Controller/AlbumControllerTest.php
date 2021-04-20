<?php

namespace AlbumTest\Controller;

use Album\Model\Album;
use Prophecy\Argument;
use Album\Model\AlbumTable;
use Laminas\ServiceManager\ServiceManager;
use Album\Controller\AlbumController;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AlbumControllerTest extends AbstractHttpControllerTestCase {
    protected $albumTable;
    protected $traceError = true;

    protected function configureServiceManager(ServiceManager $services) {
        $services->setAllowOverride(true);

        $services->setService('config', $this->updateConfig($services->get('config')));
        $services->setService(AlbumTable::class, $this->mockAlbumTable()->reveal());

        $services->setAllowOverride(false);
    }

    protected function updateConfig($config) {
        $config['db'] = [];
        return $config;
    }

    protected function mockAlbumTable() {
        $this->albumTable = $this->prophesize(AlbumTable::class);
        return $this->albumTable;
    }

    protected function setUp() : void {

        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));
        parent::setUp();

        $this->configureServiceManager($this->getApplicationServiceLocator());

        // $services = $this->getApplicationServiceLocator();
        // $config = $services->get('config');
        // unset($config['db']);
        // $services->setAllowOverride(true);
        // $services->setService('config', $config);
        // $services->setAllowOverride(false);
    }

    public function testIndexActionCanBeAccessed() {

        $this->albumTable->fetchAll()->willReturn([]);

        $this->dispatch('/album');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Album');
        $this->assertControllerName(AlbumController::class);
        $this->assertControllerClass('AlbumController');
        $this->assertMatchedRouteName('album');
    }

    public function testAddActionRedirectsAfterValidPost() {
        $this->albumTable
            ->saveAlbum(Argument::type(Album::class))
            ->shouldBeCalled();

        $postData = [
            'title'  => 'Led Zeppelin III',
            'artist' => 'Led Zeppelin',
            'id'     => '',
        ];
        $this->dispatch('/album/add', 'POST', $postData);
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/album');
    }
}