<?php
namespace StudioNet\GraphQL\Tests;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphQLType;
use StudioNet\GraphQL\GraphQL;
use StudioNet\GraphQL\Tests\Entity;

/**
 * Singleton tests
 *
 * @see TestCase
 */
class GraphQLMutationTest extends TestCase {

	/**
	 * Test mutation
	 *
	 * @return void
	 */
	public function testMutation() {
		factory(Entity\User::class, 5)->create();
		
		$graphql = app(GraphQL::class);
		$graphql->registerSchema('default', []);
		$graphql->registerDefinition(Definition\UserDefinition::class);
		$graphql->registerDefinition(Definition\PostDefinition::class);

		$this->specify('tests mutation on user', function () {
			$query = 'mutation { user(id: 1, with: { name: "toto" }) { id, name } }';
			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => [
						'id' => '1',
						'name' => 'toto',
					]
				]
			]);

			$user = Entity\User::first();
			$this->assertSame('toto', $user->name);
		});

		$this->specify('tests validation', function () {
			$query = 'mutation { user(id: 1, with: { name: "la" }) { id, name } }';
			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => null
				],
				'errors' => [
					[
						'message' => 'validation',
						'locations' => [
							[
								'line' => 1,
								'column' => 12,
							],
						],
						'validation' => [
							'name' => [
								'The name must be between 3 and 10 characters.'
							]
						]
					]
				]
			]);
		});

		$this->specify('tests drop on user', function () {
			$query = 'mutation { deleteUser(id: 1) { name }}';
			$this->assertGraphQLEquals($query, [
				'data' => [
					'deleteUser' => [
						'name' => 'toto',
					]
				]
			]);

			$user = Entity\User::find(1);
			$this->assertEmpty($user);
		});

		$this->specify('tests batch update on user', function () {
			$query = 'mutation { users(objects: [{id: 4, with: {name: "test"}}, {id: 5, with: {name: "toto"}}]) { id, name }}';
			$this->assertGraphQLEquals($query, [
				'data' => [
					'users' => [
						['id' => '4', 'name' => 'test'],
						['id' => '5', 'name' => 'toto'],
					]
				]
			]);
		});
	}

	/**
	 * Test nested add mutation
	 *
	 * @return void
	 */
	public function testNestedMutation() {
		factory(Entity\User::class, 5)->create();
		
		$graphql = app(GraphQL::class);
		$graphql->registerSchema('default', []);
		$graphql->registerDefinition(Definition\UserDefinition::class);
		$graphql->registerDefinition(Definition\PostDefinition::class);

		$this->specify('tests nested mutation on user', function () {
			$query = <<<'GQL'
mutation MutateUser {
	user(id: 1, with: { name: "toto", posts: [{title:"aa", content:"bb"}] }) {
		id,
		name,
		posts {
			title,
			content
		}
	}
}
GQL;
			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => [
						'id' => '1',
						'name' => 'toto',
						'posts' => [
							[
								'title' => 'aa',
								'content' => 'bb'
							]
						]
					]
				]
			]);

			$query = <<<'GQL'
mutation MutateUser {
	user(id: 1, with: { name: "toto", posts: [{title:"cc", content:"dd"}] }) {
		id,
		name,
		posts {
			title,
			content
		}
	}
}
GQL;

			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => [
						'id' => '1',
						'name' => 'toto',
						'posts' => [
							[
								'title' => 'aa',
								'content' => 'bb'
							],
							[
								'title' => 'cc',
								'content' => 'dd'
							]
						]
					]
				]
			]);

			$user = Entity\User::first();
			$this->assertSame('toto', $user->name);
		});
	}

	/**
	 * Test nested add mutation
	 *
	 * @return void
	 */
	public function testNestedEditMutation() {
		factory(Entity\User::class, 5)->create();
		
		$graphql = app(GraphQL::class);
		$graphql->registerSchema('default', []);
		$graphql->registerDefinition(Definition\UserDefinition::class);
		$graphql->registerDefinition(Definition\PostDefinition::class);

		$this->specify('tests nested mutation on user', function () {
			$query = <<<'GQL'
mutation MutateUser {
	user(id: 1, with: { name: "toto", posts: [{title:"aa", content:"bb"}] }) {
		id,
		name,
		posts {
			title,
			content
		}
	}
}
GQL;
			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => [
						'id' => '1',
						'name' => 'toto',
						'posts' => [
							[
								'title' => 'aa',
								'content' => 'bb'
							]
						]
					]
				]
			]);

			$query = <<<'GQL'
mutation MutateUser {
	user(id: 1, with: { name: "toto", posts: [{id: 1, title:"cc", content:"dd"}] }) {
		id,
		name,
		posts {
			title,
			content
		}
	}
}
GQL;

			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => [
						'id' => '1',
						'name' => 'toto',
						'posts' => [
							[
								'title' => 'cc',
								'content' => 'dd'
							]
						]
					]
				]
			]);

			$user = Entity\User::first();
			$this->assertSame('toto', $user->name);
		});
	}

	/**
	 * Test nested add null mutation
	 *
	 * @return void
	 */
	public function testNestedEditNullMutation() {
		factory(Entity\User::class, 5)->create();
		
		$graphql = app(GraphQL::class);
		$graphql->registerSchema('default', []);
		$graphql->registerDefinition(Definition\UserDefinition::class);
		$graphql->registerDefinition(Definition\PostDefinition::class);

		$this->specify('tests nested mutation on user', function () {
			$query = <<<'GQL'
mutation MutateUser {
	user(id: 1, with: { name: "toto", posts: [{title:"aa", content:"bb"}] }) {
		id,
		name,
		posts {
			title,
			content
		}
	}
}
GQL;
			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => [
						'id' => '1',
						'name' => 'toto',
						'posts' => [
							[
								'title' => 'aa',
								'content' => 'bb'
							]
						]
					]
				]
			]);

			$query = <<<'GQL'
mutation MutateUser {
	user(id: 1, with: { name: "toto", posts: null }) {
		id,
		name,
		posts {
			title,
			content
		}
	}
}
GQL;

			$this->assertGraphQLEquals($query, [
				'data' => [
					'user' => [
						'id' => '1',
						'name' => 'toto',
						'posts' => [
							[
								'title' => 'aa',
								'content' => 'bb'
							]
						]
					]
				]
			]);

			$user = Entity\User::first();
			$this->assertSame('toto', $user->name);
		});
	}
}
