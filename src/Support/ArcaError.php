<?php

namespace AgustinZamar\LaravelArcaSdk\Support;

final class ArcaError
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ?array $details = null,
    ) {}

    public static function fromResponse(object $error): self
    {
        return new self(
            code: (string) ($error->Code ?? 'UNKNOWN'),
            message: $error->Msg ?? 'Unknown error',
            details: (array) $error,
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'details' => $this->details,
        ];
    }
}
