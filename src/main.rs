
use juniper::{graphql_object, graphql_value, EmptyMutation, EmptySubscription, FieldResult, GraphQLEnum, GraphQLObject, RootNode, Variables};

#[derive(GraphQLEnum)]
#[derive(Clone)]
enum CatColors {
    Orange,
    Black,
    Grey
}

#[derive(GraphQLObject)]
#[graphql(description = "A very friendly animal")]
#[derive(Clone)]
struct Cat {
    name: String,
    color: CatColors
}

struct Context {
    // Use your real database pool here.
    cats: Vec<Cat>,
}

// To make our `Context` usable by `juniper`, we have to implement a marker 
// trait.
impl juniper::Context for Context {}

struct Query;

#[graphql_object]
#[graphql(context = Context)]
impl Query {
    fn cat(
        // Arguments to resolvers can either be simple scalar types, enums or 
        // input objects.
        name: String,
        // To gain access to the `Context`, we specify a `context`-named 
        // argument referring the correspondent `Context` type, and `juniper`
        // will inject it automatically.
        context: &Context,
    ) -> FieldResult<Cat> {
        let cat = context.cats.iter().find(|c| c.name == name).unwrap();
        // Return the result.
        Ok(cat.clone())
    }
}

type Schema = RootNode<'static, Query, EmptyMutation<Context>, EmptySubscription<Context>>;
fn main() {
    let mut cats = Vec::new();
    cats.push(Cat { name: "Lili".to_string(), color: CatColors::Black});
    cats.push(Cat { name: "Lulu".to_string(), color: CatColors::Grey});
    // Create a context.
    let ctx = Context {cats: cats};

    // Run the execution.
    let (res, _errors) = juniper::execute_sync(
        "query { cat (name: \"Lili\") { name, color }}",
        None,
        &Schema::new(Query, EmptyMutation::new(), EmptySubscription::new()),
        &Variables::new(),
        &ctx,
    ).unwrap();

    assert_eq!(
        res,
        graphql_value!({
            "cat": {
                "name": "Lili",
                "color": "BLACK"
            }
        }),
    );
}