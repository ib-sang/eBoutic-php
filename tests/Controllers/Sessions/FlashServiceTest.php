<?php declare(strict_types=1);

namespace Tests\Sessions;

use Controllers\Session\ArraySession;
use Controllers\Session\FlashService;
use PHPUnit\Framework\TestCase;

class FlashServiceTest extends TestCase
{

    private $session;
    private $flashService;

    public function setUp():void{
        $this->session = new ArraySession();
        $this->flashService=new FlashService($this->session);
    }

    public function testFlashDeleteAfterGettintIt(){
        $this->flashService->success('Bravo');
        $this->assertEquals('Bravo',$this->flashService->get('success'));
        $this->assertNull($this->session->get('flash'));
        $this->assertEquals('Bravo',$this->flashService->get('success'));
        $this->assertEquals('Bravo',$this->flashService->get('success'));
    }

}