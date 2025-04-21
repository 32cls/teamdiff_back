<?php

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
            'participants' => [
                'type' => Type::listOf(GraphQL::type('Participant')),
                'description' => 'History of participation of the summoner',
                'args' => [
                    'order' => [
                        'type' => GraphQL::type('OrderEnum'),
                    ],
                ],
                'query' => function (array $args, $query, $ctx): void {
                    if (isset($args['order'])) {
                        $query
                            ->join('lolmatches', 'participants.match_id', '=', 'lolmatches.id')
                            ->orderBy('lolmatches.game_creation', $args['order'])
                            ->select('participants.*'); // Important to avoid messing with Eloquent hydration
                    }
                },
            ],
            'reviewSummary' => [
                'type' => GraphQL::type('ReviewSummary'),
                'description' => 'Summary of reviews for this summoner',
                'selectable' => false,
                'resolve' => function ($root) {
                    $reviews = $root->participants
                        ->flatMap(function ($participant) {
                            return $participant->receivedReviews;
                        });

                    if ($reviews->isEmpty()) {
                        return [
                            'averageRating' => null,
                            'averageRatingPerChampion' => [],
                        ];
                    }

                    $average = round($reviews->avg('rating'), 2);

                    $perChampion = $root->participants
                        ->groupBy('champion_id')
                        ->map(function ($group, $championId) {
                            $allReviews = $group
                                ->flatMap(function ($participant) {
                                    return $participant->receivedReviews;
                                });

                            return [
                                'championId' => $championId,
                                'averageRating' => $allReviews->isNotEmpty() ? round($allReviews->avg('rating'), 2) : null,
                            ];
                        })
                        ->values();

                    return [
                        'averageRating' => $average,
                        'averageRatingPerChampion' => $perChampion,
                    ];
                },
            ],
        ];
    }

}
