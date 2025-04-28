<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Summoner;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class SummonerType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Summoner',
        'description' => 'A summoner as defined by Riot API',
        'model' => Summoner::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'Identifier of the summoner',
            ],
            'icon' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Icon id of the summoner',
            ],
            'level' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Experience level of the summoner',
            ],
            'account' => [
                'type' => Type::nonNull(GraphQL::type('Account')),
                'description' => 'The account associated with the summoner',
            ],
            'participations' => [
                'type' => Type::listOf(GraphQL::type('Participation')),
                'description' => 'History of participations of the summoner',
                'args' => [
                    'gameCreationDateOrder' => [
                        'type' => GraphQL::type('OrderEnum'),
                    ],
                ],
                'query' => function (array $args, $query): void {
                    if (isset($args['order'])) {
                        $query
                            ->join('lolmatches', 'participations.matchId', '=', 'lolmatches.id')
                            ->orderBy('lolmatches.gameCreation', $args['gameCreationDateOrder'])
                            ->select('participations.*'); // Important to avoid messing with Eloquent hydration
                    }
                },
            ],
            'reviewSummary' => [
                'type' => GraphQL::type('ReviewSummary'),
                'description' => 'Summary of reviews for this summoner',
                'selectable' => false,
                'args' => [
                    'bestRatingOrder' => [
                        'type' => GraphQL::type('OrderEnum'),
                    ],
                ],
                'resolve' => function ($root, array $args) {
                    $reviews = $root->participations
                        ->flatMap(function ($participation) {
                            return $participation->receivedReviews;
                        });

                    if ($reviews->isEmpty()) {
                        return null;
                    }

                    $average = round($reviews->avg('rating'), 2);
                    $count = $reviews->count();

                    $perChampion = $root->participations
                        ->filter(fn ($participation) => $participation->receivedReviews->isNotEmpty())
                        ->groupBy('championId')
                        ->map(function ($group, $championId) {
                            $allReviews = $group->flatMap(fn ($participation) => $participation->receivedReviews);

                            return [
                                'count' => $allReviews->count(),
                                'championId' => $championId,
                                'rating' => round($allReviews->avg('rating'), 2),
                            ];
                        });

                    if (! isset($args['bestRatingOrder']) || $args['bestRatingOrder'] === 'desc') {
                        $perChampion = $perChampion->sortByDesc('rating');
                    } else {
                        $perChampion = $perChampion->sort('rating');
                    }

                    $perChampion = $perChampion->values();

                    return [
                        'totalRatingCount' => $count,
                        'totalAverageRating' => $average,
                        'averageRatingPerChampion' => $perChampion,
                    ];
                },
            ],
        ];
    }
}
