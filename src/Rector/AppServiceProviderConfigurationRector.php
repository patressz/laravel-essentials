<?php

namespace Patressz\Essentials\Rector;

use Patressz\Essentials\Enums\ConfigureOption;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;

final class AppServiceProviderConfigurationRector extends AbstractRector
{
    /**
     * List of methods to be added to the boot method of the service provider.
     *
     * @var array<string, string>
     */
    private array $methods = [
        'configureDates' => 'Configure the dates.',
        'configureModels' => 'Configure the models.',
        'configureCommands' => 'Configure the application\'s commands.',
        'configureHttpScheme' => 'Configure the HTTP scheme.',
        'configureAssetPrefetching' => 'Configure asset prefetching.',
    ];

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! str_ends_with($this->getName($node), 'ServiceProvider')) {
            return null;
        }

        $bootMethod = $this->getBootMethod($node);

        if ($bootMethod === null) {
            return null;
        }

        $selectedOptions = explode(',', $_ENV['SELECTED_OPTIONS']);

        foreach ($selectedOptions as $method) {
            $methodName = ConfigureOption::from($method)->getConfigurationMethod();

            $this->addMethodCallToBootMethod($node, $bootMethod, $methodName);
            $this->addBlankMethod($node, $methodName, $this->methods[$methodName]);
            $this->configureMethod($node, $methodName, $method);
        }

        return $node;
    }

    private function getBootMethod(Class_ $class): ?ClassMethod
    {
        foreach ($class->getMethods() as $method) {
            if ($this->getName($method) === 'boot') {
                return $method;
            }
        }

        return null;
    }

    private function addMethodCallToBootMethod(Class_ $class, ClassMethod $bootMethod, string $methodName): void
    {
        $availableMethods = collect($class->getMethods())
            ->map(fn (ClassMethod $methodName) => $this->getName($methodName))
            ->all();

        if (in_array($methodName, $availableMethods, true)) {
            return;
        }

        $methodCall = new MethodCall(
            new Variable('this'),
            new Identifier($methodName)
        );

        $bootMethod->stmts[] = new Expression($methodCall);
    }

    private function addBlankMethod(Class_ $class, string $methodName, string $methodDescription): void
    {
        $availableMethods = collect($class->getMethods())
            ->map(fn (ClassMethod $methodName) => $this->getName($methodName))
            ->all();

        if (in_array($methodName, $availableMethods, true)) {
            return;
        }

        $configureModelsMethod = new ClassMethod(new Identifier($methodName));
        $configureModelsMethod->flags = Modifiers::PRIVATE;
        $configureModelsMethod->returnType = new Identifier('void');

        $configureModelsMethod->setDocComment(new Doc(<<<DOC

        /**
        * {$methodDescription}
        */
        DOC));

        $class->stmts[] = $configureModelsMethod;
    }

    private function configureMethod(Class_ $class, string $methodName, string $method): void
    {
        $configureMethod = $class->getMethod($methodName);

        if ($configureMethod === null) {
            return;
        }

        match ($method) {
            ConfigureOption::CONFIGURE_MODELS_STRICTNESS->value => $this->configureModelsStrictness($configureMethod),
            ConfigureOption::CONFIGURE_MODELS_UNGUARDED->value => $this->configureModelsUnguarded($configureMethod),
            ConfigureOption::CONFIGURE_MODELS_AUTOMATICALLY_EAGER_LOADING_RELATIONSHIPS->value => $this->configureModelsAutomaticallyEagerLoadingRelationships($configureMethod),
            ConfigureOption::CONFIGURE_DATES->value => $this->configureDates($configureMethod),
            ConfigureOption::CONFIGURE_COMMANDS->value => $this->configureCommands($configureMethod),
            ConfigureOption::CONFIGURE_HTTP_SCHEME->value => $this->configureHttpScheme($configureMethod),
            ConfigureOption::CONFIGURE_ASSET_PREFETCHING->value => $this->configureAssetPrefetching($configureMethod),
        };
    }

    private function configureDates(ClassMethod $classMethod): void
    {
        $expression = new Expression(
            new StaticCall(
                new Name('\Illuminate\Support\Facades\Date'),
                new Identifier('use'),
                [
                    new Name('\Carbon\CarbonImmutable::class'),
                ]
            )
        );

        $classMethod->stmts[] = $expression;
    }

    private function configureModelsStrictness(ClassMethod $classMethod): void
    {
        $expression = new Expression(
            new StaticCall(
                new Name('\Illuminate\Database\Eloquent\Model'),
                new Identifier('shouldBeStrict'),
                [
                    new Arg(
                        new MethodCall(
                            new FuncCall(new Name('app')),
                            new Identifier('isProduction')
                        )
                    ),
                ]
            )
        );

        $classMethod->stmts[] = $expression;
    }

    private function configureModelsUnguarded(ClassMethod $classMethod): void
    {
        $expression = new Expression(
            new StaticCall(
                new Name('\Illuminate\Database\Eloquent\Model'),
                new Identifier('unguard')
            )
        );

        $classMethod->stmts[] = $expression;
    }

    private function configureModelsAutomaticallyEagerLoadingRelationships(ClassMethod $classMethod): void
    {
        $expression = new Expression(
            new StaticCall(
                new Name('\Illuminate\Database\Eloquent\Model'),
                new Identifier('automaticallyEagerLoadRelationships'),
                [
                    new Arg(
                        new BooleanNot(
                            new MethodCall(
                                new FuncCall(new Name('app')),
                                new Identifier('isProduction')
                            )
                        )
                    ),
                ]
            )
        );

        $classMethod->stmts[] = $expression;
    }

    private function configureCommands(ClassMethod $classMethod): void
    {
        $expression = new Expression(
            new StaticCall(
                new Name('\Illuminate\Support\Facades\DB'),
                new Identifier('prohibitDestructiveCommands'),
                [
                    new Arg(
                        new MethodCall(
                            new FuncCall(new Name('app')),
                            new Identifier('isProduction')
                        )
                    ),
                ]
            )
        );

        $classMethod->stmts[] = $expression;
    }

    private function configureHttpScheme(ClassMethod $classMethod): void
    {
        $ifStatement = new If_(
            new MethodCall(
                new FuncCall(new Name('app')),
                new Identifier('isProduction')
            ),
            [
                'stmts' => [
                    new Expression(
                        new StaticCall(
                            new Name('\Illuminate\Support\Facades\URL'),
                            new Identifier('forceScheme'),
                            [new Arg(new String_('https'))]
                        )
                    ),
                ],
            ]
        );

        $classMethod->stmts[] = $ifStatement;
    }

    private function configureAssetPrefetching(ClassMethod $classMethod): void
    {
        $expression = new Expression(
            new StaticCall(
                new Name('\Illuminate\Support\Facades\Vite'),
                new Identifier('prefetch'),
                [
                    new Arg(
                        new Int_(3),
                        name: new Identifier('concurrency')
                    ),
                ]
            )
        );

        $classMethod->stmts[] = $expression;
    }
}
