------------
What is Fjor
------------

Fjor is a library for PHP that makes it easy to handle dependencies. Your application
typically is wired together by a web of objects that interact. The construction of
that web can be handled by Fjor.

----------
Using Fjor
----------

Before you can use Fjor you need to install it and create an instance of Fjor.

Installation
============

Installation is possible by either downloading it or by installation through
Composer.

### downloading ###

Fjor can be downloaded from [github](https://github.com/koenhoeymans/Fjor/tags). It
contains an autoloader so all you need to be able to set up a Fjor instance (see below)
is loading it:

	require_once __DIR__
		. DIRECTORY_SEPARATOR . 'to'
		. DIRECTORY_SEPARATOR . 'Fjor'
		. DIRECTORY_SEPARATOR . 'Autoload.php';

### through composer ###

If you don't know yet about Composer you may want to read an
[introduction](http://getcomposer.org/doc/00-intro.md) first. After you installed
composer create a `composer.json` file with at least the following:

	{
		"require": {
			"fjor/fjor": "0.1.*"
		}
	}


Setting up Fjor
===============

The default setup for Fjor is simply

	$fjor = \Fjor\Fjor::defaultSetup();

The above code will create a new instance of Fjor using a fluent interface and make
it easily pluggable.

Getting objects
===============

Getting an instance of a class is straightforward:

	$fjor->get('StdClass');

The [StdClass](http://php.net/manual/en/reserved.classes.php) has no constructor
dependencies and is rather trivial to create.

If only one instance is needed:

	$fjor->setSingleton('MyClass');

This will ensure the same instance is returned every time.

Dependencies are automatically injected if a class is specified as a type hint. In the
following example Fjor knows an instance of `Bar` is needed:

	class Foo {
		public function __construct(Bar $bar) {}
	}

	class Bar
	{}

If a parameter is optional there will be no value injected if not specified:

	class Foo {
		public function __construct(Bar $bar = null) {}
	}

If a parameter type hint is an interface you can tell Fjor what class to use as
implementation. Given following:

	interface Hero {
		public function help($name);
	}

	class Batman implements Hero {
		public function help($name) {
			echo "Starting batmobile on my way to help $name"; 
		}
	}

	class Movie {
		private $hero;

		public function __construct(Hero $hero) {
			$this->hero = $hero;
		}

		public function addHeroicAction() {
			$this->hero->help('citizen in trouble');
		}
	}

Constructing a movie presents a problem to Fjor because it can't instantiate an
interface. We need to specify what implementation it should use when it encounters
a `Hero`:

	$fjor->given('Hero')->thenUse('Batman');

When Fjor now encounters a `Hero` it knows we want to use `Batman` as our hero. If
you already have a real Batman you can register him as implementation too:

	$batman = new Batman();
	$fjor->given('Hero')->thenUse($batman);

Everytime Fjor needs a `Hero` it will use this instance. If you would like Fjor to
create one instance only of a certain class or interface you don't need to have
your own instance and register that. This will do:

	$fjor->setSingleton('Batman');

Fjor will now create only one instance of Batman and use it whenever a `Batman` is
needed. The same is true for interfaces:

	$fjor->setSingleton('Hero');
	$fjor->given('Hero')->thenUse('Batman');

This will provide only one `Batman` instance every time a `Hero` is called for.

The previous examples will allow to construct an object when a type hint is specified
as interface or (abstract) class. This will ensure that constructor dependencies are
injected automagically. The objects will be autowired together. Sometimes you need to
specify another type of constructor value though, such as an integer when an ID is
asked for.

	class Catwomen {
		public function __construct($numberOfLives) {}
	}

If you want to use Catwomen giving her nine lives you can do it with this code:

	$fjor->given('Catwomen')->constructWith(array(9));

`ConstructWith` takes an array as argument filled with the values that Fjor will
use upon construction of the object. The keys determine the position of the argument,
using 0 as first argument. This is also the way to specify which position the value
should be injected:

	class Batman {
		public function __construct(Sidekick $sidekick, $name) {}
	}

	$fjor->given('Batman')->constructWith(array(1 => 'Bruce Wayne'));

Now Fjor will create Batman with 'Bruce Wayne' as second argument value. This works
also for bindings. Given the same Batman specification as above you can specify that
`Batman` needs to be constructed with `Robin` as sidekick:

	class Robin implements Sidekick {}

	$fjor->given('Batman')->constructWith(array('Robin', 'Bruce Wayne'));

Fjor is smart enough to understand that the first constructor argument for `Batman`
needs to be an object of type `Sidekick`. It will first look at the arguments that
were specified for construction of the object before it will look at what was specified
as a general binding.

Not only constructor arguments can be specified. If you need to add values to a given
method right after creation it's possible to tell Fjor to inject them:

	$fjor->given('Batman')->andMethod('goGet')->addParam($joker);

After Fjor has created Batman it will call method `goGet` with `$joker` as parameter. As
with constructors this can be a primitive value or the name of a class Fjor should create
first and then inject.

Multiple injections in a method are possible:

	$fjor->given('Batman')->andMethod('goGet')
		->addParam($joker)
		->addParam($harleyQuinn);

Best Practices
==============

### Object Graph Construction ###

When setting up your project there is a web of objects that collaborate. Often
the relationships are defined through the constructor. These dependencies are
what an object needs to fulfill its responsibilities. Upon instantiation of your
main class these dependencies need to be constructed. Fjor helps in making this
initial setup easy to manage.

### Object Factories ###

Not all objects are constructed from the beginning. Some objects are only created
when the need arises or after is determined what specific object will be needed
to handle a job. Factories are great to encapsulate the creational logic. When
they know what type of object needs to be created Fjor is there to create it,
taking responsibility of its creation.

### How not to use Fjor ###

Generally you should try to avoid depending on Fjor for getting objects instead
of specifying the service you needed in the first place:

	public function login(User $user, Fjor $fjor) {
		$logger = $fjor->get('Logger');
		$logger->log('trying to log in user ' . $user->getName());
	}

In the above code we actually don't need Fjor to do what we want. Fjor is added
as a dependency to give us what we need (the logger). What we should do is
ask for the logger instead of Fjor:

	public function login(User $user, Logger $logger) {
		$logger->log('trying to log in user ' . $user->getName());
	}
