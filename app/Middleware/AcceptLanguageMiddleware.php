<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Support\Filesystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AcceptLanguageMiddleware implements MiddlewareInterface
{
    protected array $supportedLanguage = [];

    public function __construct(protected ConfigInterface $config, protected Filesystem $filesystem, protected TranslatorInterface $translator) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->supportedLanguage();

        $language = $this->getRequestLanguage($request->getHeaderLine('Accept-Language'));

        if (!is_null($language)) {
            $this->translator->setLocale($language);
        }

        return $handler->handle($request);
    }

    protected function supportedLanguage(): void
    {
        if (!empty($this->supportedLanguage)) {
            return;
        }

        // @phpstan-ignore-next-line
        $path = $this->config->get('translation.path', BASE_PATH.'/storage/languages');
        $this->supportedLanguage = array_map(fn ($lang) => str_replace($path.'/', '', $lang), $this->filesystem->directories($path));
    }

    protected function getRequestLanguage(string $acceptLanguages): ?string
    {
        if (empty($acceptLanguages)) {
            return null;
        }

        $languages = [];

        foreach (explode(',', $acceptLanguages) as $acceptLanguage) {
            $languageOptions = explode(';', trim($acceptLanguage));
            $q = str_replace('q=', '', $languageOptions[1] ?? 'q=1');

            $languages[$q] = $languageOptions[0];
        }

        krsort($languages, SORT_NUMERIC);

        foreach ($languages as $language) {
            if (in_array($language, $this->supportedLanguage)) {
                return $language;
            }
        }

        return null;
    }
}
