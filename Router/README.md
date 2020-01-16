# Routing
##Intro##
Mezon provides simple routing class for your needs.

##Simple routes##

Example fot this paragraph can be found in %mezon-path%/doc/examples/router/index.php

To be hounest you have already used it. But it was called implicitly.

Router allows you to map URLs on your php code and call when ever it needs to be calld.

Router supports simple routes like in the example above - example.com/contacts/

Each Application object implicity creates routes for it's 'action_[action-name]' methods, where 'action-name' will be stored as a route. Here is small (au usual)) ) example:

```PHP
class           MySite
{
    /**
    *   Main page.
    */
    public function action_index()
    {
        return( 'This is the main page of our simple site' );
    }

    /**
    *   Contacts page.
    */
    public function action_contacts()
    {
        return( 'This is the "Contacts" page' );
    }

    /**
    *   Some custom action handler.
    */
    public function some_other_page()
    {
        return( 'Some other page of our site' );
    }
}
```

And this code

```PHP
$Router = new Router();
$Router->fetch_actions( $MySite = new MySite() );
```

will create router object and loads information about it's actions and create routes. Strictly it will create two routes, because the class MySite has only two methods wich start wth 'action_prefix'. Method 'some_other_page' will not be converted into route automatically.

But we can still use this method as a route handler:

```PHP
$Router->add_route( 'some_any_other_route' , array( $MySite , 'some_other_page' ) );
```

We just need to create it explicitly.

We can also use simple functions for route creation:

```PHP
function        sitemap()
{
    return( 'Some fake sitemap' );
}

$Router->add_route( 'sitemap' , 'sitemap' );
```

##One handler for all routes##

You can specify one processor for all routes like this:

```PHP
$Router->add_route( '*' , function(){} );
```

Note that routing search will stops if the '*' handler will be found. For example:

```PHP
$Router->add_route( '*' , function(){} );
$Router->add_route( '/index/' , function(){} );
```

In this example route /index/ will never be reached. All request will be passed to the '*' handler. But in this example:

```PHP
$Router->add_route( '/contacts/' , function(){} );
$Router->add_route( '*' , function(){} );
$Router->add_route( '/index/' , function(){} );
```

route /contacts/ will be processed by it's own handler, and all other routes (even /index/) will be processed by the '*' handler.

##Route variables##

And now a little bit more complex routes:

```PHP
$Router->add_route( '/catalogue/[i:cat_id]/' , function( $Route , $Variables ){} );
$Router->add_route( '/catalogue/[a:cat_name]/' , function( $Route , $Variables ){} );
```

Here:

i - any integer number
a - any [a-z0-9A-Z_\/\-\.\@]+ string
il - comma separated list of integer ids
s - any string

All this variables are passed as second function parameter wich is named in the example above - $Variales. All variables are passed as an associative array.

##Request types and first steps to the REST API##

You can bind handlers to different request types as shown bellow:

```PHP
$Router->add_route( '/contacts/' , function(){} , 'POST' ); // this handler will be called for POST requests
$Router->add_route( '/contacts/' , function(){} , 'GET' );  // this handler will be called for GET requests
$Router->add_route( '/contacts/' , function(){} , 'PUT' );  // this handler will be called for PUT requests
$Router->add_route( '/contacts/' , function(){} , 'DELETE' );  // this handler will be called for DELETE requests
```