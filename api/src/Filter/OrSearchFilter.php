<?php

// src/Filter/OrSearchFilter.php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter as FilterAbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class OrSearchFilter extends FilterAbstractFilter
{

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!$this->isPropertyEnabled($property, $resourceClass) || null === $value) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->orWhere(sprintf('%s.%s LIKE :search', $alias, 'nom'))
            ->orWhere(sprintf('%s.%s LIKE :search', $alias, 'reference'))
            ->setParameter('search', '%' . $value . '%');
    }

    // protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, string $resourceClass, string $operationName = null)


    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => 'search',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'swagger' => [
                    'description' => 'Search in name or description',
                    'type' => 'string',
                    'name' => 'search',
                ],
            ],
        ];
    }
}
