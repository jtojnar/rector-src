<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @implements AnnotationToAttributeMapperInterface<mixed[]>
 */
final class ArrayAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    private AnnotationToAttributeMapper $annotationToAttributeMapper;

    /**
     * Avoid circular reference
     */
    #[Required]
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper): void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }

    public function isCandidate(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * @param mixed[] $value
     */
    public function map($value): array|Expr
    {
        $values = array_map(fn ($item): Expr|array => $this->annotationToAttributeMapper->map($item), $value);

        foreach ($values as $key => $value) {
            // remove the key and value? useful in case of unwrapping nested attributes
            if (! $this->isRemoveArrayPlaceholder($value)) {
                continue;
            }

            unset($values[$key]);
        }

        return $values;
    }

    private function isRemoveArrayPlaceholder(Expr|array $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        return in_array(DocTagNodeState::REMOVE_ARRAY, $value, true);
    }
}
