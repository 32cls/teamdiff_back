<?php

namespace App\GraphQL\Queries;

use App\Models\Account;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class AccountQuery extends Query
{

    public function type(): Type
    {
        return GraphQL::type("Account");
    }

    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        if (isset($args['name'], $args['tag'])) {
            return Account::where('id' , $args['id'])->get();
        } else {
            return null;
        }
    }
}
