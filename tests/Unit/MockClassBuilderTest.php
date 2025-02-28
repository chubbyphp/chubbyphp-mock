<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\MockClassBuilder;
use Chubbyphp\Tests\Mock\Sample\ByReference;
use Chubbyphp\Tests\Mock\Sample\DefaultParameters;
use Chubbyphp\Tests\Mock\Sample\Sample;
use Chubbyphp\Tests\Mock\Sample\Variadic;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Chubbyphp\Mock\MockClassBuilder
 *
 * @internal
 */
final class MockClassBuilderTest extends TestCase
{
    public function testWithSample(): void
    {
        $builder = new MockClassBuilder();

        $mockClassName = $builder->mock(Sample::class);

        $reflectionClass = new \ReflectionClass($mockClassName);

        $cwd = getcwd();

        self::assertSame(
            <<<EOT
                Class [ <user> final class Chubbyphp_Tests_Mock_Sample_Sample_Mock extends Chubbyphp\\Tests\\Mock\\Sample\\Sample ] {
                  @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 1-12

                  - Constants [0] {
                  }

                  - Static properties [0] {
                  }

                  - Static methods [0] {
                  }

                  - Properties [1] {
                    Property [ private Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                  }

                  - Methods [4] {
                    Method [ <user, overwrites Chubbyphp\\Tests\\Mock\\Sample\\Sample, ctor> public method __construct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 2 - 2

                      - Parameters [1] {
                        Parameter #0 [ <required> Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                      }
                    }

                    Method [ <user> public method __destruct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 4 - 4
                    }

                    Method [ <user, overwrites Chubbyphp\\Tests\\Mock\\Sample\\Sample, prototype Chubbyphp\\Tests\\Mock\\Sample\\Sample> public method setPrevious ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 8 - 8

                      - Parameters [1] {
                        Parameter #0 [ <required> Chubbyphp\\Tests\\Mock\\Sample\\Sample \$previous ]
                      }
                      - Return [ Chubbyphp\\Tests\\Mock\\Sample\\Sample ]
                    }

                    Method [ <user, overwrites Chubbyphp\\Tests\\Mock\\Sample\\Sample, prototype Chubbyphp\\Tests\\Mock\\Sample\\Sample> public method getPrevious ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 10 - 10

                      - Parameters [0] {
                      }
                      - Return [ ?Chubbyphp\\Tests\\Mock\\Sample\\Sample ]
                    }
                  }
                }

                EOT,
            (string) $reflectionClass
        );
    }

    public function testWithByReference(): void
    {
        $builder = new MockClassBuilder();

        $mockClassName = $builder->mock(ByReference::class);

        $reflectionClass = new \ReflectionClass($mockClassName);

        $cwd = getcwd();

        self::assertSame(
            <<<EOT
                Class [ <user> final class Chubbyphp_Tests_Mock_Sample_ByReference_Mock extends Chubbyphp\\Tests\\Mock\\Sample\\ByReference ] {
                  @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 1-8

                  - Constants [0] {
                  }

                  - Static properties [0] {
                  }

                  - Static methods [0] {
                  }

                  - Properties [1] {
                    Property [ private Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                  }

                  - Methods [3] {
                    Method [ <user, ctor> public method __construct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 2 - 2

                      - Parameters [1] {
                        Parameter #0 [ <required> Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                      }
                    }

                    Method [ <user> public method __destruct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 4 - 4
                    }

                    Method [ <user, overwrites Chubbyphp\\Tests\\Mock\\Sample\\ByReference, prototype Chubbyphp\\Tests\\Mock\\Sample\\ByReference> public method toLower ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 6 - 6

                      - Parameters [1] {
                        Parameter #0 [ <required> string &\$text ]
                      }
                      - Return [ Chubbyphp\\Tests\\Mock\\Sample\\ByReference ]
                    }
                  }
                }

                EOT,
            (string) $reflectionClass
        );
    }

    public function testWithVariadic(): void
    {
        $builder = new MockClassBuilder();

        $mockClassName = $builder->mock(Variadic::class);

        $reflectionClass = new \ReflectionClass($mockClassName);

        $cwd = getcwd();

        self::assertSame(
            <<<EOT
                Class [ <user> final class Chubbyphp_Tests_Mock_Sample_Variadic_Mock extends Chubbyphp\\Tests\\Mock\\Sample\\Variadic ] {
                  @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 1-8

                  - Constants [0] {
                  }

                  - Static properties [0] {
                  }

                  - Static methods [0] {
                  }

                  - Properties [1] {
                    Property [ private Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                  }

                  - Methods [3] {
                    Method [ <user, ctor> public method __construct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 2 - 2

                      - Parameters [1] {
                        Parameter #0 [ <required> Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                      }
                    }

                    Method [ <user> public method __destruct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 4 - 4
                    }

                    Method [ <user, overwrites Chubbyphp\\Tests\\Mock\\Sample\\Variadic, prototype Chubbyphp\\Tests\\Mock\\Sample\\Variadic> public method join ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 6 - 6

                      - Parameters [2] {
                        Parameter #0 [ <required> string \$separator ]
                        Parameter #1 [ <optional> ...\$strings ]
                      }
                      - Return [ string ]
                    }
                  }
                }

                EOT,
            (string) $reflectionClass
        );
    }

    public function testWithDefaultParameters(): void
    {
        $builder = new MockClassBuilder();

        $mockClassName = $builder->mock(DefaultParameters::class);

        $reflectionClass = new \ReflectionClass($mockClassName);

        $cwd = getcwd();

        self::assertSame(
            <<<EOT
                Class [ <user> final class Chubbyphp_Tests_Mock_Sample_DefaultParameters_Mock extends Chubbyphp\\Tests\\Mock\\Sample\\DefaultParameters ] {
                  @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 1-10

                  - Constants [6] {
                    Constant [ public null NULL ] {  }
                    Constant [ public bool BOOLEAN ] { 1 }
                    Constant [ public int INT ] { 5 }
                    Constant [ public float FLOAT ] { 9.81 }
                    Constant [ public string STRING ] { string }
                    Constant [ public array ARRAY ] { Array }
                  }

                  - Static properties [0] {
                  }

                  - Static methods [1] {
                    Method [ <user, overwrites Chubbyphp\\Tests\\Mock\\Sample\\DefaultParameters, prototype Chubbyphp\\Tests\\Mock\\Sample\\DefaultParameters> static public method staticDefaultParameters ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 8 - 8

                      - Parameters [25] {
                        Parameter #0 [ <optional> null \$null = NULL ]
                        Parameter #1 [ <optional> null \$nullWithCustomConst = NULL ]
                        Parameter #2 [ <optional> bool \$booleanTrue = true ]
                        Parameter #3 [ <optional> bool \$booleanFalse = false ]
                        Parameter #4 [ <optional> ?bool \$optionalBooleanTrue = NULL ]
                        Parameter #5 [ <optional> bool \$booleanWithConst = ZEND_THREAD_SAFE ]
                        Parameter #6 [ <optional> bool \$booleanWithCustomConst = true ]
                        Parameter #7 [ <optional> int \$int = 42 ]
                        Parameter #8 [ <optional> ?int \$optionalInt = NULL ]
                        Parameter #9 [ <optional> int \$intWithConst = PHP_INT_MIN ]
                        Parameter #10 [ <optional> int \$intWithCustomConst = 5 ]
                        Parameter #11 [ <optional> float \$float = 3.14159 ]
                        Parameter #12 [ <optional> ?float \$optionalFloat = NULL ]
                        Parameter #13 [ <optional> float \$floatWithConst = PHP_FLOAT_MIN ]
                        Parameter #14 [ <optional> float \$floatWithCustomConst = 9.81 ]
                        Parameter #15 [ <optional> string \$string = 'string' ]
                        Parameter #16 [ <optional> ?string \$optionalString = NULL ]
                        Parameter #17 [ <optional> string \$stringWithConst = PHP_EOL ]
                        Parameter #18 [ <optional> string \$stringWithCustomConst = 'string' ]
                        Parameter #19 [ <optional> array \$array = ['null' => NULL, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'] ]
                        Parameter #20 [ <optional> ?array \$optionalArray = NULL ]
                        Parameter #21 [ <optional> array \$arrayWithCustomConst = ['null' => NULL, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'] ]
                        Parameter #22 [ <optional> DateTimeImmutable \$dateTimeImmutable = new \\DateTimeImmutable('2025-02-16T00:25:30+01:00', new \\DateTimeZone('Europe/Zurich')) ]
                        Parameter #23 [ <optional> ArrayIterator \$arrayIterator = new \\ArrayIterator(['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string']) ]
                        Parameter #24 [ <optional> Chubbyphp\\Tests\\Mock\\Sample\\Sample \$sample = new \\Chubbyphp\\Tests\\Mock\\Sample\\Sample('name', 'value') ]
                      }
                      - Return [ void ]
                    }
                  }

                  - Properties [1] {
                    Property [ private Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                  }

                  - Methods [3] {
                    Method [ <user, ctor> public method __construct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 2 - 2

                      - Parameters [1] {
                        Parameter #0 [ <required> Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                      }
                    }

                    Method [ <user> public method __destruct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 4 - 4
                    }

                    Method [ <user, overwrites Chubbyphp\\Tests\\Mock\\Sample\\DefaultParameters, prototype Chubbyphp\\Tests\\Mock\\Sample\\DefaultParameters> public method defaultParameters ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 6 - 6

                      - Parameters [25] {
                        Parameter #0 [ <optional> null \$null = NULL ]
                        Parameter #1 [ <optional> null \$nullWithCustomConst = NULL ]
                        Parameter #2 [ <optional> bool \$booleanTrue = true ]
                        Parameter #3 [ <optional> bool \$booleanFalse = false ]
                        Parameter #4 [ <optional> ?bool \$optionalBooleanTrue = NULL ]
                        Parameter #5 [ <optional> bool \$booleanWithConst = ZEND_THREAD_SAFE ]
                        Parameter #6 [ <optional> bool \$booleanWithCustomConst = true ]
                        Parameter #7 [ <optional> int \$int = 42 ]
                        Parameter #8 [ <optional> ?int \$optionalInt = NULL ]
                        Parameter #9 [ <optional> int \$intWithConst = PHP_INT_MIN ]
                        Parameter #10 [ <optional> int \$intWithCustomConst = 5 ]
                        Parameter #11 [ <optional> float \$float = 3.14159 ]
                        Parameter #12 [ <optional> ?float \$optionalFloat = NULL ]
                        Parameter #13 [ <optional> float \$floatWithConst = PHP_FLOAT_MIN ]
                        Parameter #14 [ <optional> float \$floatWithCustomConst = 9.81 ]
                        Parameter #15 [ <optional> string \$string = 'string' ]
                        Parameter #16 [ <optional> ?string \$optionalString = NULL ]
                        Parameter #17 [ <optional> string \$stringWithConst = PHP_EOL ]
                        Parameter #18 [ <optional> string \$stringWithCustomConst = 'string' ]
                        Parameter #19 [ <optional> array \$array = ['null' => NULL, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'] ]
                        Parameter #20 [ <optional> ?array \$optionalArray = NULL ]
                        Parameter #21 [ <optional> array \$arrayWithCustomConst = ['null' => NULL, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'] ]
                        Parameter #22 [ <optional> DateTimeImmutable \$dateTimeImmutable = new \\DateTimeImmutable('2025-02-16T00:25:30+01:00', new \\DateTimeZone('Europe/Zurich')) ]
                        Parameter #23 [ <optional> ArrayIterator \$arrayIterator = new \\ArrayIterator(['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string']) ]
                        Parameter #24 [ <optional> Chubbyphp\\Tests\\Mock\\Sample\\Sample \$sample = new \\Chubbyphp\\Tests\\Mock\\Sample\\Sample('name', 'value') ]
                      }
                      - Return [ void ]
                    }
                  }
                }

                EOT,
            (string) $reflectionClass
        );
    }

    public function testWithPingRequestHandler(): void
    {
        $builder = new MockClassBuilder();

        $mockClassName = $builder->mock(ServerRequestInterface::class);

        $reflectionClass = new \ReflectionClass($mockClassName);

        $cwd = getcwd();

        self::assertSame(
            <<<EOT
                Class [ <user> final class Psr_Http_Message_ServerRequestInterface_Mock implements Psr\\Http\\Message\\ServerRequestInterface, Psr\\Http\\Message\\MessageInterface, Psr\\Http\\Message\\RequestInterface ] {
                  @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 1-66

                  - Constants [0] {
                  }

                  - Static properties [0] {
                  }

                  - Static methods [0] {
                  }

                  - Properties [1] {
                    Property [ private Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                  }

                  - Methods [32] {
                    Method [ <user, ctor> public method __construct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 2 - 2

                      - Parameters [1] {
                        Parameter #0 [ <required> Chubbyphp\\Mock\\MockMethods \$mockMethods ]
                      }
                    }

                    Method [ <user> public method __destruct ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 4 - 4
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method getServerParams ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 6 - 6

                      - Parameters [0] {
                      }
                      - Return [ array ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method getCookieParams ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 8 - 8

                      - Parameters [0] {
                      }
                      - Return [ array ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method withCookieParams ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 10 - 10

                      - Parameters [1] {
                        Parameter #0 [ <required> array \$cookies ]
                      }
                      - Return [ Psr\\Http\\Message\\ServerRequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method getQueryParams ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 12 - 12

                      - Parameters [0] {
                      }
                      - Return [ array ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method withQueryParams ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 14 - 14

                      - Parameters [1] {
                        Parameter #0 [ <required> array \$query ]
                      }
                      - Return [ Psr\\Http\\Message\\ServerRequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method getUploadedFiles ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 16 - 16

                      - Parameters [0] {
                      }
                      - Return [ array ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method withUploadedFiles ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 18 - 18

                      - Parameters [1] {
                        Parameter #0 [ <required> array \$uploadedFiles ]
                      }
                      - Return [ Psr\\Http\\Message\\ServerRequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method getParsedBody ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 20 - 20
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method withParsedBody ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 22 - 22

                      - Parameters [1] {
                        Parameter #0 [ <required> \$data ]
                      }
                      - Return [ Psr\\Http\\Message\\ServerRequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method getAttributes ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 24 - 24

                      - Parameters [0] {
                      }
                      - Return [ array ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method getAttribute ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 26 - 26

                      - Parameters [2] {
                        Parameter #0 [ <required> string \$name ]
                        Parameter #1 [ <optional> \$default = NULL ]
                      }
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method withAttribute ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 28 - 28

                      - Parameters [2] {
                        Parameter #0 [ <required> string \$name ]
                        Parameter #1 [ <required> \$value ]
                      }
                      - Return [ Psr\\Http\\Message\\ServerRequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\ServerRequestInterface> public method withoutAttribute ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 30 - 30

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$name ]
                      }
                      - Return [ Psr\\Http\\Message\\ServerRequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\RequestInterface> public method getRequestTarget ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 32 - 32

                      - Parameters [0] {
                      }
                      - Return [ string ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\RequestInterface> public method withRequestTarget ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 34 - 34

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$requestTarget ]
                      }
                      - Return [ Psr\\Http\\Message\\RequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\RequestInterface> public method getMethod ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 36 - 36

                      - Parameters [0] {
                      }
                      - Return [ string ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\RequestInterface> public method withMethod ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 38 - 38

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$method ]
                      }
                      - Return [ Psr\\Http\\Message\\RequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\RequestInterface> public method getUri ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 40 - 40

                      - Parameters [0] {
                      }
                      - Return [ Psr\\Http\\Message\\UriInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\RequestInterface> public method withUri ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 42 - 42

                      - Parameters [2] {
                        Parameter #0 [ <required> Psr\\Http\\Message\\UriInterface \$uri ]
                        Parameter #1 [ <optional> bool \$preserveHost = false ]
                      }
                      - Return [ Psr\\Http\\Message\\RequestInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method getProtocolVersion ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 44 - 44

                      - Parameters [0] {
                      }
                      - Return [ string ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method withProtocolVersion ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 46 - 46

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$version ]
                      }
                      - Return [ Psr\\Http\\Message\\MessageInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method getHeaders ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 48 - 48

                      - Parameters [0] {
                      }
                      - Return [ array ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method hasHeader ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 50 - 50

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$name ]
                      }
                      - Return [ bool ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method getHeader ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 52 - 52

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$name ]
                      }
                      - Return [ array ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method getHeaderLine ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 54 - 54

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$name ]
                      }
                      - Return [ string ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method withHeader ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 56 - 56

                      - Parameters [2] {
                        Parameter #0 [ <required> string \$name ]
                        Parameter #1 [ <required> \$value ]
                      }
                      - Return [ Psr\\Http\\Message\\MessageInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method withAddedHeader ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 58 - 58

                      - Parameters [2] {
                        Parameter #0 [ <required> string \$name ]
                        Parameter #1 [ <required> \$value ]
                      }
                      - Return [ Psr\\Http\\Message\\MessageInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method withoutHeader ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 60 - 60

                      - Parameters [1] {
                        Parameter #0 [ <required> string \$name ]
                      }
                      - Return [ Psr\\Http\\Message\\MessageInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method getBody ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 62 - 62

                      - Parameters [0] {
                      }
                      - Return [ Psr\\Http\\Message\\StreamInterface ]
                    }

                    Method [ <user, prototype Psr\\Http\\Message\\MessageInterface> public method withBody ] {
                      @@ {$cwd}/src/MockClassBuilder.php(37) : eval()'d code 64 - 64

                      - Parameters [1] {
                        Parameter #0 [ <required> Psr\\Http\\Message\\StreamInterface \$body ]
                      }
                      - Return [ Psr\\Http\\Message\\MessageInterface ]
                    }
                  }
                }

                EOT,
            (string) $reflectionClass
        );
    }

    public function testWithDateTimeImmutable(): void
    {
        $builder = new MockClassBuilder();

        $mockClassName = $builder->mock(\DateTimeImmutable::class);

        $reflectionClass = new \ReflectionClass($mockClassName);

        $reflectionClassAsString = (string) $reflectionClass;

        $lines = [
            'Class [ <user> final class DateTimeImmutable_Mock extends DateTimeImmutable implements DateTimeInterface ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> static public method __set_state ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> static public method getLastErrors ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> static public method createFromMutable ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> static public method createFromInterface ] {',
            'Method [ <user, overwrites DateTimeImmutable, ctor> public method __construct ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeInterface> public method format ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeInterface> public method getTimezone ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeInterface> public method getOffset ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeInterface> public method getTimestamp ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeInterface> public method diff ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method modify ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method add ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method sub ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method setTimezone ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method setTime ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method setDate ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method setISODate ] {',
            'Method [ <user, overwrites DateTimeImmutable, prototype DateTimeImmutable> public method setTimestamp ] {',
        ];

        foreach ($lines as $line) {
            self::assertStringContainsString($line, $reflectionClassAsString);
        }
    }
}
