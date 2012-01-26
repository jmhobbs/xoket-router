<?php
	require_once( 'simpletest/autorun.php' );
	require_once( 'route.php' );

	class TestCreation extends UnitTestCase {
		
			function testAdd () {
				Route::flush();
				Route::add( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
			}

			function testGet () {
				Route::flush();
				Route::get( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
			}

			function testPost () {
				Route::flush();
				Route::post( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
			}

			function testPut () {
				Route::flush();
				Route::put( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
			}

			function testDelete () {
				Route::flush();
				Route::delete( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
			}

	}

	class TestResponds extends UnitTestCase {
		
			function testAdd () {
				Route::flush();
				Route::add( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
				$this->assertTrue( Route::find( 'test' )->respondsTo( Route::ANY ) );
			}

			function testGet () {
				Route::flush();
				Route::get( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
				$this->assertTrue( Route::find( 'test' )->respondsTo( Route::GET ) );
				$this->assertFalse( Route::find( 'test' )->respondsTo( Route::POST & Route::PUT & Route::DELETE ) );
			}

			function testPost () {
				Route::flush();
				Route::post( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
				$this->assertTrue( Route::find( 'test' )->respondsTo( Route::POST ) );
				$this->assertFalse( Route::find( 'test' )->respondsTo( Route::GET & Route::PUT & Route::DELETE ) );
			}

			function testPut () {
				Route::flush();
				Route::put( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
				$this->assertTrue( Route::find( 'test' )->respondsTo( Route::PUT ) );
				$this->assertFalse( Route::find( 'test' )->respondsTo( Route::POST & Route::GET & Route::DELETE ) );
			}

			function testDelete () {
				Route::flush();
				Route::delete( 'test', 'test' );
				$this->assertIsA( Route::find( 'test' ), 'Route' );
				$this->assertTrue( Route::find( 'test' )->respondsTo( Route::DELETE ) );
				$this->assertFalse( Route::find( 'test' )->respondsTo( Route::POST & Route::PUT & Route::GET ) );
			}

	}

	class TestMatches extends UnitTestCase {

		function setUp () {
			Route::flush();

			Route::add(
				'photos', 
				'photos(/:id(/:action))', 
				array( 'id' => '[0-9]+', 'action' => '[a-z]+' ) 
			)->defaults( array( 'controller' => 'photos', 'action' => 'index' ) );

			Route::add(
				'error', 
				'(:directory/)error/:action(/:message)',
				array( 'action' => '[0-9]{3}' )
			)->defaults( array( 'controller' => 'error' ) );

			Route::add( 
				'api', 
				'api/v:version/:controller(/:action)', 
				array( 'version' => '[0-9]+' )
			)->defaults( array( 'controller' => 'api' ) );
		}

		function testPhotos () {
			$match = Route::match( 'photos' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'photos' );
			$this->assertEqual( $match['action'], 'index' );

			$match = Route::match( 'photos/5' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'photos' );
			$this->assertEqual( $match['action'], 'index' );
			$this->assertEqual( $match['id'], '5' );
		}

	}

	class TestMatchPrecedence extends UnitTestCase {
		
		function setUp () {
			Route::flush();
			Route::add(
				'photos_bare', 
				'photos'
			)->defaults( array( 'controller' => 'bare' ) );

			Route::add( 
				'photos_precise', 
				'photos(/:id(/:action))', 
				array( 'id' => '[0-9]+', 'action' => '[a-z]+' ) 
			)->defaults( array( 'controller' => 'precise' ) );

			Route::add( 
				'photos_loose', 
				'photos(/:id(/:action))' 
			)->defaults( array( 'controller' => 'loose' ) );

			Route::add( 
				'photos_unreachable', 
				'photos(/:id(/:action))', 
				array( 'id' => '[0-9]+', 'action' => '[a-z]+' )
			)->defaults( array( 'controller' => 'unreachable' ) );
		}

		function testBare () {
			$match = Route::match( 'photos' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'bare' );
		}

		function testPrecise () {
			$match = Route::match( 'photos/5/view' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'precise' );
		}

		function testLoose () {
			$match = Route::match( 'photos/loljk/view' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'loose' );
		}

		function testUnreachable () {
			$match = Route::match( 'photos/5/view' );
			$this->assertIsA( $match, 'array' );
			$this->assertNotEqual( $match['controller'], 'loose' );
		}

	}

	class TestGetters extends UnitTestCase {

		function testGetDefaults () {
			Route::flush();
			$route = Route::add( 'test', 'test' );
			$this->assertSame( Route::parameters(), $route->defaults() );
		}

		function testGetMethods () {
			Route::flush();
			$route = Route::add( 'test', 'test', null, Route::POST );
			$this->assertEqual( Route::POST, $route->methods() );
		}

		function testGetConstraints () {
			Route::flush();
			$constraints = array( 'id' => '[0-9]+' );
			$route = Route::add( 'test', 'test', $constraints );
			$this->assertSame( $constraints, $route->constraints() );
		}

	}

	class TestSetters extends UnitTestCase {

		function testSetDefaults () {
			Route::flush();
			$defaults = array(
				'directory'  => 'directory',
				'controller' => 'controller',
				'action'     => 'action',
			);
			$route = Route::add( 'test', 'test' );
			$route->defaults( $defaults );
			$this->assertSame( $defaults, $route->defaults() );
		}

		function testSetMethods () {
			Route::flush();
			$route = Route::add( 'test', 'test', null, Route::POST );
			$this->assertEqual( Route::POST, $route->methods() );
			$route->methods( Route::GET );
			$this->assertEqual( Route::GET, $route->methods() );
		}

		function testSetConstraints () {
			Route::flush();
			$constraints = array( 'id' => '[0-9]+' );
			$route = Route::add( 'test', 'test', $constraints );
			$this->assertSame( $constraints, $route->constraints() );

			$constraints['id'] = '[a-z]+';
			$route->constraints( $constraints );
			$this->assertSame( $constraints, $route->constraints() );
		}

	}

