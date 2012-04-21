<?php

	namespace xoket;

	class Route {

		const ANY    = 15;
		const GET    =  1;
		const POST   =  2;
		const PUT    =  4;
		const DELETE =  8;

		const DEFAULT_CONSTRAINT = '[^\/]+';

		protected static $_defaults = array(
			'directory'  => null,
			'controller' => null,
			'action'     => null,
		);

		protected $methods     = self::ANY;
		protected $route       = null;
		protected $constraints = array();
		protected $regex       = null;
		protected $defaults    = array();

		/*!
			Build a new route.
		*/
		protected function __construct( $methods, $route, $constraints ) {
			if( ! is_array( $constraints ) ) { $constraints = array(); }
			$this->methods     = $methods;
			$this->route       = $route;
			$this->constraints = $constraints;
			$this->compile();
		}

		/*!
			Get or set the methods allowed for this route.
		*/
		public function methods ( $methods = null ) {
			if( is_null( $methods ) ) {
				return $this->methods;
			}
			else {
				$this->methods = $methods;
			}
			return $this;
		}

		/*!
			Get or set the route parameter constraints.
		*/
		public function constraints ( $constraints = null ) {
			if( is_null( $constraints ) ) {
				return $this->constraints;
			}
			else {
				$this->constraints = $constraints;
				$this->compile();
			}
			return $this;
		}
		
		/*!
			Get or set the default parameter values for this route.
		*/
		public function defaults ( $defaults = null ) {
			if( is_null( $defaults ) ) {
				return array_merge( self::$_defaults, $this->defaults );
			}
			else {
				$this->defaults = $defaults;
			}
			return $this;
		}

		/*!
			Turns the route and any constraints into a regular expression for matching.
		*/
		protected function compile () {
			// Start with the described route
			$regex = $this->route;

			// Make paren'd groups optional
			$regex = str_replace( ')', ')?', $regex );

			// Find all the :parameters
			if( preg_match_all( '/:([a-z]+)/i', $this->route, $matches ) ) { 
				foreach( $matches[1] as $name ) {
					// Get our constraint for this match
					$constraint = ( isset( $this->constraints[$name] ) ) ? $this->constraints[$name] : self::DEFAULT_CONSTRAINT;
					// Replace the match with a named match
					$regex = preg_replace( '#:' . $name . '#', '(?P<' . $name . '>' . $constraint . ')', $regex );
				}
			}
			
			// Use octothorpe as a delimiter (it's illegal in a path because URI fragments never get sent to the server)
			$regex = '#^' . $regex . '$#';

			// Copy it into use
			$this->regex = $regex;
		}

		/*!
			Test if this route responds to HTTP verb(s)

			\param methods The verb(s) to check.

			\returns matched True or False accordingly.
		*/
		public function respondsTo ( $methods ) {
			return 0 !== ( $methods & $this->methods );
		}

		/*!
			Compare this route to a path and HTTP method set.

			\param path The path to check.
			\param methods The HTTP verb(s) to check.

			\returns parameters An array of matched parameters, or false if not a match.
		*/
		public function compare ( $path, $methods ) {
			// Only run if it's a valid method for this route.
			if( ! $this->respondsTo( $methods ) ) { return false; }

			// Now compare to the regex and return what's needed.
			if( preg_match( $this->regex, $path, $matches ) ) {
				// Matched! Now we need to get the data out of it.
				// Only named matches are important to us.
				$parameters = array();
				foreach( $matches as $key => $value ) {
					if( is_string( $key ) ) { $parameters[$key] = $value; }
				}
				return array_merge( self::$_defaults, $this->defaults, $parameters );
			}
			return false;
		}

		/*!
			Get the current regex, useful for debugging.

			\returns regex A string with the regular expression.
		*/
		public function getRegex () {
			$this->compile(); // Re-compile to be sure
			return $this->regex;
		}

		/////////////////////////////
		// Static Stuff

		protected static $routes = array();

		/*!
			Creates a new route.

			\param name The name of the route.
			\param route The route, in proper format.
			\param constraints An array of parameter constraints (optional)
			\param methods The HTTP verbs that this route responds to.

			\returns route A reference to the route.
		*/
		public static function & add ( $name, $route, $constraints = null, $methods = self::ANY ) {
			$route = new self( $methods, $route, $constraints );
			self::$routes[$name] = &$route;
			return $route;
		}

		/*!
			Add a route for the GET HTTP verb.
			\see Route::add
		*/
		public static function & get ( $route, $name = null, $constraints = null ) {
			return self::add( $name, $route, $constraints, self::GET );
		}

		/*!
			Add a route for the POST HTTP verb.
			\see Route::add
		*/
		public static function & post ( $route, $name = null, $constraints = null ) {
			return self::add( $name, $route, $constraints, self::POST );
		}

		/*!
			Add a route for the PUT HTTP verb.
			\see Route::add
		*/
		public static function & put ( $route, $name = null, $constraints = null ) {
			return self::add( $name, $route, $constraints, self::PUT );
		}

		/*!
			Add a route for the DELETE HTTP verb.
			\see Route::add
		*/
		public static function & delete ( $route, $name = null, $constraints = null ) {
			return self::add( $name, $route, $constraints, self::DELETE );
		}

		/*!
			You know those routes you had? They're gone now.
		*/
		public static function flush () {
			self::$routes = array();
		}

		/*!
			Attempt to match a path to a route.

			\param path The path to match, e.g. photos/5/edit
			\param methods The HTTP verb(s) to match on (optional)
		*/
		public static function match ( $path, $methods = self::ANY ) {
			foreach( self::$routes as $route ) {
				$match = $route->compare( $path, $methods );
				if( false !== $match ) { return $match; }
			}
			return false;
		}

		/*!
			Get a reference to a named route.

			\param name The name of the route.

			\returns route A reference to the route, or false if not found.
		*/
		public static function & find ( $name ) {
			return ( isset( self::$routes[$name] ) ) ? self::$routes[$name] : false;
		}

		/*!
			Get/Set the Route class parameter defaults.

			\param defaults The replacement parameter defaults. (optional)
		*/
		public static function parameters ( $defaults = null ) {
			if( is_null( $defaults ) ) {
				return self::$_defaults;
			}
			else {
				self::$_defaults = $defaults;
			}
		}

	}

