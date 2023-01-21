<?php

namespace App\Helpers;

use App\Models\User;
use App\Services\PointsCalculator;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilder;

class RatingHelper
{
    public static function getCalculatedRating(): Collection
    {
        $sort = request()->get('sort');
        /** @var \Illuminate\Support\Collection|\App\Models\User[] $users */
        $users = QueryBuilder::for(User::class)
            ->defaultSort('name')
            ->allowedSorts($sort ?? 'name')
            ->withCount([
                'readChapters',
                'exerciseMembers' => fn($query) => $query->finished(),
            ])
            ->where('read_chapters_count', '>', 0)
            ->orWhere('exercise_members_count', '>', 0)
            ->limit(100)
            ->get();
            $calculatedRating = $users->map(fn(User $user) => [
                'user' => $user,
                'points' => PointsCalculator::calculate($user->read_chapters_count, $user->exercise_members_count),
            ])
            ->when(empty($sort), function ($collection) {
                return $collection->sortByDesc('points');
            })
            ->values()
            ->keyBy(fn($ratingPosition, $index) => $index + 1);

        return $calculatedRating;
    }

    public static function getParameterFromSorting(string $column, ?string $state): array
    {
        return match ($state) {
            null => ['sort' => $column],
            $column => ['sort' => "-{$column}"],
            default => [],
        };
    }
}
