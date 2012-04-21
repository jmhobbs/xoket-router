<?php
	require_once( 'simpletest/autorun.php' );
	require_once( 'src/xoket/route.php' );

	class TestCreation extends UnitTestCase {
		
			function testAdd () {
				xoket\Route::flush();
				xoket\Route::add( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
			}

			function testGet () {
				xoket\Route::flush();
				xoket\Route::get( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
			}

			function testPost () {
				xoket\Route::flush();
				xoket\Route::post( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
			}

			function testPut () {
				xoket\Route::flush();
				xoket\Route::put( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
			}

			function testDelete () {
				xoket\Route::flush();
				xoket\Route::delete( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
			}

	}

	class TestResponds extends UnitTestCase {
		
			function testAdd () {
				xoket\Route::flush();
				xoket\Route::add( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
				$this->assertTrue( xoket\Route::find( 'test' )->respondsTo( xoket\Route::ANY ) );
			}

			function testGet () {
				xoket\Route::flush();
				xoket\Route::get( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
				$this->assertTrue( xoket\Route::find( 'test' )->respondsTo( xoket\Route::GET ) );
				$this->assertFalse( xoket\Route::find( 'test' )->respondsTo( xoket\Route::POST & xoket\Route::PUT & xoket\Route::DELETE ) );
			}

			function testPost () {
				xoket\Route::flush();
				xoket\Route::post( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
				$this->assertTrue( xoket\Route::find( 'test' )->respondsTo( xoket\Route::POST ) );
				$this->assertFalse( xoket\Route::find( 'test' )->respondsTo( xoket\Route::GET & xoket\Route::PUT & xoket\Route::DELETE ) );
			}

			function testPut () {
				xoket\Route::flush();
				xoket\Route::put( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
				$this->assertTrue( xoket\Route::find( 'test' )->respondsTo( xoket\Route::PUT ) );
				$this->assertFalse( xoket\Route::find( 'test' )->respondsTo( xoket\Route::POST & xoket\Route::GET & xoket\Route::DELETE ) );
			}

			function testDelete () {
				xoket\Route::flush();
				xoket\Route::delete( 'test', 'test' );
				$this->assertIsA( xoket\Route::find( 'test' ), 'xoket\Route' );
				$this->assertTrue( xoket\Route::find( 'test' )->respondsTo( xoket\Route::DELETE ) );
				$this->assertFalse( xoket\Route::find( 'test' )->respondsTo( xoket\Route::POST & xoket\Route::PUT & xoket\Route::GET ) );
			}

	}

	class TestMatches extends UnitTestCase {

		function setUp () {
			xoket\Route::flush();

			xoket\Route::add(
				'photos', 
				'photos(/:id(/:action))', 
				array( 'id' => '[0-9]+', 'action' => '[a-z]+' ) 
			)->defaults( array( 'controller' => 'photos', 'action' => 'index' ) );

			xoket\Route::add(
				'error', 
				'(:directory/)error/:action(/:message)',
				array( 'action' => '[0-9]{3}' )
			)->defaults( array( 'controller' => 'error' ) );

			xoket\Route::add( 
				'api', 
				'api/v:version/:controller(/:action)', 
				array( 'version' => '[0-9]+' )
			)->defaults( array( 'controller' => 'api' ) );
		}

		function testPhotos () {
			$match = xoket\Route::match( 'photos' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'photos' );
			$this->assertEqual( $match['action'], 'index' );

			$match = xoket\Route::match( 'photos/5' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'photos' );
			$this->assertEqual( $match['action'], 'index' );
			$this->assertEqual( $match['id'], '5' );
		}

	}

	class TestMatchPrecedence extends UnitTestCase {
		
		function setUp () {
			xoket\Route::flush();
			xoket\Route::add(
				'photos_bare', 
				'photos'
			)->defaults( array( 'controller' => 'bare' ) );

			xoket\Route::add( 
				'photos_precise', 
				'photos(/:id(/:action))', 
				array( 'id' => '[0-9]+', 'action' => '[a-z]+' ) 
			)->defaults( array( 'controller' => 'precise' ) );

			xoket\Route::add( 
				'photos_loose', 
				'photos(/:id(/:action))' 
			)->defaults( array( 'controller' => 'loose' ) );

			xoket\Route::add( 
				'photos_unreachable', 
				'photos(/:id(/:action))', 
				array( 'id' => '[0-9]+', 'action' => '[a-z]+' )
			)->defaults( array( 'controller' => 'unreachable' ) );
		}

		function testBare () {
			$match = xoket\Route::match( 'photos' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'bare' );
		}

		function testPrecise () {
			$match = xoket\Route::match( 'photos/5/view' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'precise' );
		}

		function testLoose () {
			$match = xoket\Route::match( 'photos/loljk/view' );
			$this->assertIsA( $match, 'array' );
			$this->assertEqual( $match['controller'], 'loose' );
		}

		function testUnreachable () {
			$match = xoket\Route::match( 'photos/5/view' );
			$this->assertIsA( $match, 'array' );
			$this->assertNotEqual( $match['controller'], 'loose' );
		}

	}

	class TestGetters extends UnitTestCase {

		function testGetDefaults () {
			xoket\Route::flush();
			$route = xoket\Route::add( 'test', 'test' );
			$this->assertSame( xoket\Route::parameters(), $route->defaults() );
		}

		function testGetMethods () {
			xoket\Route::flush();
			$route = xoket\Route::add( 'test', 'test', null, xoket\Route::POST );
			$this->assertEqual( xoket\Route::POST, $route->methods() );
		}

		function testGetConstraints () {
			xoket\Route::flush();
			$constraints = array( 'id' => '[0-9]+' );
			$route = xoket\Route::add( 'test', 'test', $constraints );
			$this->assertSame( $constraints, $route->constraints() );
		}

	}

	class TestSetters extends UnitTestCase {

		function testSetDefaults () {
			xoket\Route::flush();
			$defaults = array(
				'directory'  => 'directory',
				'controller' => 'controller',
				'action'     => 'action',
			);
			$route = xoket\Route::add( 'test', 'test' );
			$route->defaults( $defaults );
			$this->assertSame( $defaults, $route->defaults() );
		}

		function testSetMethods () {
			xoket\Route::flush();
			$route = xoket\Route::add( 'test', 'test', null, xoket\Route::POST );
			$this->assertEqual( xoket\Route::POST, $route->methods() );
			$route->methods( xoket\Route::GET );
			$this->assertEqual( xoket\Route::GET, $route->methods() );
		}

		function testSetConstraints () {
			xoket\Route::flush();
			$constraints = array( 'id' => '[0-9]+' );
			$route = xoket\Route::add( 'test', 'test', $constraints );
			$this->assertSame( $constraints, $route->constraints() );

			$constraints['id'] = '[a-z]+';
			$route->constraints( $constraints );
			$this->assertSame( $constraints, $route->constraints() );
		}

	}

