<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\UserController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    private $userController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userController = $this->mock(UserController::class)->makePartial();
    }

    public function testIndex()
    {
        $request = new Request(['page' => 'test-page']);
        $testObject = (object)['testKey' => 'testValue'];
        Http::shouldReceive('get->object')->once()
            ->with('https://reqres.in/api/users?page=test-page')
            ->withNoArgs()
            ->andReturn($testObject);
        $response = $this->userController->index($request);
        $this->assertEquals(['users' => $testObject], $response->getData());
        $this->assertEquals('users.index', $response->getName());
        $this->assertInstanceOf(View::class, $response);
    }

    public function testCreate()
    {
        $response = $this->userController->create();
        $this->assertEquals('users.create', $response->getName());
        $this->assertInstanceOf(View::class, $response);
    }

    public function testShow()
    {
        $testObject = (object)['data' => 'test'];
        Http::shouldReceive('get->object')->once()
            ->with('https://reqres.in/api/users/101')
            ->withNoArgs()
            ->andReturn($testObject);
        $response = $this->userController->show(101);
        $this->assertEquals(['user' => 'test'], $response->getData());
        $this->assertEquals('users.show', $response->getName());
        $this->assertInstanceOf(View::class, $response);
    }

    public function testStoreWhenHttpPostReturnsSuccessfulResponse()
    {
        $testUserData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.local'
        ];
        $request = new Request($testUserData);
        $this->userController->shouldReceive('validate')->once()
            ->with(
                $request,
                [
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'email' => 'required|email',
                ]
            )->andReturnTrue();
        $mockResponse = $this->mock('MockResponse');
        $mockResponse->shouldReceive('successful')->once()
            ->withNoArgs()
            ->andReturnTrue();
        $mockResponse->shouldReceive('object')->once()
            ->withNoArgs()
            ->andReturn((object)array_merge(['id' => 1001], $testUserData));
        Http::shouldReceive('post')->once()
            ->with('https://reqres.in/api/users', $testUserData)
            ->andReturn($mockResponse);

        $redirect = $this->userController->store($request);
        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertEquals('User 1001 created', $redirect->getSession()->get('success'));
    }

    public function testStoreWhenHttpPostReturnsUnsuccessfulResponse()
    {
        $testUserData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.local'
        ];
        $request = new Request($testUserData);
        $this->userController->shouldReceive('validate')->once()->andReturnTrue();

        Http::shouldReceive('post->successful')->once()
            ->with('https://reqres.in/api/users', $testUserData)
            ->withNoArgs()
            ->andReturnFalse();

        $redirect = $this->userController->store($request);
        $this->assertEquals(config('app.url'), $redirect->getTargetUrl());
        $this->assertInstanceOf(RedirectResponse::class, $redirect);
    }
}
