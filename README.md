# What is this?

It's a [PCRE](http://php.net/PCRE) based routing engine for PHP.

# Where did it come from?

I wrote it from scratch as an exercise after dinner.  It was fun.

# How does it work?

You specify routes, which are strings like so:

    photos/:id(/:action)

The :id and :action are named parameters, and you can do a couple things with them.

You can specify constraints for what they will match:

    xoket\Route::add( 'photos/:id(/:action)' )
      ->constraints( array( 'id' => '[0-9]+' ) );

and you can specify defaults:

    xoket\Route::add( 'photos/:id(/:action)' )
      ->defaults( array( 'controller' => 'photo', 'action' => 'view' ) );

Now that you have routes, you can match on them:

    xoket\Route::match( 'photos/5/delete' );

Route::match will either return false if no match is found, or an array of parameters, like so:

    array(
      'directory'  => '',
      'controller' => 'photo',
      'action'     => 'delete',
      'id'         => '5',
    )

The use of which is only limited by your imagination.

