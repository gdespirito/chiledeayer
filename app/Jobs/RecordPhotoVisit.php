<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Models\PhotoVisit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordPhotoVisit implements ShouldQueue
{
    use Queueable;

    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners',
        'facebookexternalhit', 'twitterbot', 'linkedinbot',
        'whatsapp', 'telegrambot', 'googlebot', 'bingbot',
        'yandex', 'baidu', 'duckduck', 'semrush', 'ahref',
        'lighthouse', 'pagespeed', 'headless',
    ];

    /**
     * @param  array{ip_address: string, user_agent: string|null, referer: string|null, timezone: string|null}  $requestData
     */
    public function __construct(
        public int $photoId,
        public ?int $userId,
        public array $requestData,
    ) {}

    public function handle(): void
    {
        $userAgent = $this->requestData['user_agent'] ?? '';
        $isBot = $this->detectBot($userAgent);

        PhotoVisit::create([
            'photo_id' => $this->photoId,
            'user_id' => $this->userId,
            'ip_address' => $this->requestData['ip_address'],
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 512) : null,
            'referer' => isset($this->requestData['referer']) ? mb_substr($this->requestData['referer'], 0, 1024) : null,
            'timezone' => $this->requestData['timezone'] ?? null,
            'is_bot' => $isBot,
            'visited_at' => now(),
        ]);

        if (! $isBot) {
            Photo::where('id', $this->photoId)->increment('visits_count');
        }
    }

    private function detectBot(string $userAgent): bool
    {
        $lower = strtolower($userAgent);

        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
