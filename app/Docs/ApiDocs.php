<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'Calon Mantu API', version: 'v1', description: 'API documentation for Calon Mantu')]
#[OA\Server(url: '/', description: 'Default Server')]
#[OA\SecurityScheme(securityScheme: 'BearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'JWT')]
class ApiDocs
{
    #[OA\Get(
        path: '/api/health',
        summary: 'Health check',
        tags: ['System'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function health(): void
    {
    }

    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Register user',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'username', 'password', 'password_confirmation', 'full_name', 'join_date', 'role_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                    new OA\Property(property: 'full_name', type: 'string'),
                    new OA\Property(property: 'store_id', type: 'integer'),
                    new OA\Property(property: 'join_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'role_id', type: 'integer'),
                ],
                type: 'object'
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function register(): void
    {
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Login with username',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ],
                type: 'object'
            )
        ),
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function login(): void
    {
    }

    #[OA\Get(path: '/api/me', summary: 'Get current user', security: [['BearerAuth' => []]], tags: ['Auth'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function me(): void
    {
    }

    #[OA\Get(path: '/api/me/stores', summary: 'List stores accessible by current user', security: [['BearerAuth' => []]], tags: ['Auth'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function myStores(): void
    {
    }

    #[OA\Post(path: '/api/me/current-store', summary: 'Set current active store', security: [['BearerAuth' => []]], tags: ['Auth'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id'], properties: [new OA\Property(property: 'store_id', type: 'integer')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 403, description: 'Forbidden')])]
    public function updateMyCurrentStore(): void
    {
    }

    #[OA\Post(path: '/api/auth/logout', summary: 'Logout', security: [['BearerAuth' => []]], tags: ['Auth'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function logout(): void
    {
    }

    #[OA\Get(path: '/api/roles', summary: 'List roles', tags: ['Roles'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listRoles(): void
    {
    }

    #[OA\Post(
        path: '/api/roles',
        summary: 'Create role',
        tags: ['Roles'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['role_name'], properties: [new OA\Property(property: 'role_name', type: 'string')], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createRole(): void
    {
    }

    #[OA\Get(path: '/api/roles/{id}', summary: 'Get role', tags: ['Roles'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showRole(): void
    {
    }

    #[OA\Put(path: '/api/roles/{id}', summary: 'Update role', tags: ['Roles'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['role_name'], properties: [new OA\Property(property: 'role_name', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateRole(): void
    {
    }

    #[OA\Delete(path: '/api/roles/{id}', summary: 'Delete role', tags: ['Roles'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteRole(): void
    {
    }

    #[OA\Get(path: '/api/stores', summary: 'List stores', tags: ['Stores'], parameters: [new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listStores(): void
    {
    }

    #[OA\Post(path: '/api/stores', summary: 'Create store', tags: ['Stores'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_name', 'code'], properties: [new OA\Property(property: 'store_name', type: 'string'), new OA\Property(property: 'code', type: 'string'), new OA\Property(property: 'address', type: 'string'), new OA\Property(property: 'phone', type: 'string'), new OA\Property(property: 'is_active', type: 'boolean')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createStore(): void
    {
    }

    #[OA\Get(path: '/api/stores/{id}', summary: 'Get store', tags: ['Stores'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showStore(): void
    {
    }

    #[OA\Put(path: '/api/stores/{id}', summary: 'Update store', tags: ['Stores'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [new OA\Property(property: 'store_name', type: 'string'), new OA\Property(property: 'code', type: 'string'), new OA\Property(property: 'address', type: 'string'), new OA\Property(property: 'phone', type: 'string'), new OA\Property(property: 'is_active', type: 'boolean')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateStore(): void
    {
    }

    #[OA\Delete(path: '/api/stores/{id}', summary: 'Delete store', tags: ['Stores'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 422, description: 'Store has products or orders')])]
    public function deleteStore(): void
    {
    }

    #[OA\Get(path: '/api/employees', summary: 'List employees', tags: ['Employees'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'role_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listEmployees(): void
    {
    }

    #[OA\Post(
        path: '/api/employees',
        summary: 'Create employee',
        tags: ['Employees'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['full_name', 'store_id', 'email', 'join_date', 'role_id', 'username', 'password', 'password_confirmation', 'ktp', 'kk'],
                    properties: [
                        new OA\Property(property: 'full_name', type: 'string'),
                        new OA\Property(property: 'store_id', type: 'integer'),
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                        new OA\Property(property: 'join_date', type: 'string', format: 'date'),
                        new OA\Property(property: 'role_id', type: 'integer'),
                        new OA\Property(property: 'username', type: 'string'),
                        new OA\Property(property: 'password', type: 'string', format: 'password'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                        new OA\Property(property: 'ktp', type: 'string', format: 'binary'),
                        new OA\Property(property: 'kk', type: 'string', format: 'binary'),
                    ],
                    type: 'object'
                )
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createEmployee(): void
    {
    }

    #[OA\Get(path: '/api/employees/{id}', summary: 'Get employee', tags: ['Employees'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showEmployee(): void
    {
    }

    #[OA\Put(path: '/api/employees/{id}', summary: 'Update employee', tags: ['Employees'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateEmployee(): void
    {
    }

    #[OA\Delete(path: '/api/employees/{id}', summary: 'Delete employee', tags: ['Employees'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteEmployee(): void
    {
    }

    #[OA\Get(path: '/api/attendances', summary: 'List attendances', security: [['BearerAuth' => []]], tags: ['Attendances'], parameters: [new OA\Parameter(name: 'employee_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['hadir', 'izin', 'sakit', 'alpha'])), new OA\Parameter(name: 'from_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'to_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listAttendances(): void
    {
    }

    #[OA\Get(path: '/api/attendances/summary', summary: 'Get attendance summary', security: [['BearerAuth' => []]], tags: ['Attendances'], parameters: [new OA\Parameter(name: 'employee_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'from_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'to_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function attendanceSummary(): void
    {
    }

    #[OA\Get(path: '/api/attendances/today', summary: 'Get today attendance', security: [['BearerAuth' => []]], tags: ['Attendances'], parameters: [new OA\Parameter(name: 'employee_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 422, description: 'Employee required')])]
    public function todayAttendance(): void
    {
    }

    #[OA\Post(path: '/api/attendances/clock-in', summary: 'Clock in attendance', security: [['BearerAuth' => []]], tags: ['Attendances'], requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(properties: [new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'photo', type: 'string', format: 'binary'), new OA\Property(property: 'location_coordinates', type: 'string'), new OA\Property(property: 'notes', type: 'string')], type: 'object'))), responses: [new OA\Response(response: 201, description: 'Clock in success'), new OA\Response(response: 422, description: 'Already clocked in')])]
    public function clockInAttendance(): void
    {
    }

    #[OA\Post(path: '/api/attendances/clock-out', summary: 'Clock out attendance', security: [['BearerAuth' => []]], tags: ['Attendances'], requestBody: new OA\RequestBody(required: false, content: new OA\JsonContent(properties: [new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'location_coordinates', type: 'string'), new OA\Property(property: 'notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'Clock out success'), new OA\Response(response: 422, description: 'Not clocked in or already clocked out')])]
    public function clockOutAttendance(): void
    {
    }

    #[OA\Post(path: '/api/attendances', summary: 'Create attendance manually', security: [['BearerAuth' => []]], tags: ['Attendances'], requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(required: ['employee_id', 'date', 'status'], properties: [new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'date', type: 'string', format: 'date'), new OA\Property(property: 'clock_in', type: 'string', example: '08:00:00'), new OA\Property(property: 'clock_out', type: 'string', example: '17:00:00'), new OA\Property(property: 'photo', type: 'string', format: 'binary'), new OA\Property(property: 'photo_url', type: 'string'), new OA\Property(property: 'status', type: 'string', enum: ['hadir', 'izin', 'sakit', 'alpha']), new OA\Property(property: 'location_coordinates', type: 'string'), new OA\Property(property: 'notes', type: 'string')], type: 'object'))), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createAttendance(): void
    {
    }

    #[OA\Get(path: '/api/attendances/{id}', summary: 'Get attendance', security: [['BearerAuth' => []]], tags: ['Attendances'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showAttendance(): void
    {
    }

    #[OA\Put(path: '/api/attendances/{id}', summary: 'Update attendance', security: [['BearerAuth' => []]], tags: ['Attendances'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(required: ['employee_id', 'date', 'status'], properties: [new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'date', type: 'string', format: 'date'), new OA\Property(property: 'clock_in', type: 'string', example: '08:00:00'), new OA\Property(property: 'clock_out', type: 'string', example: '17:00:00'), new OA\Property(property: 'photo', type: 'string', format: 'binary'), new OA\Property(property: 'photo_url', type: 'string'), new OA\Property(property: 'status', type: 'string', enum: ['hadir', 'izin', 'sakit', 'alpha']), new OA\Property(property: 'location_coordinates', type: 'string'), new OA\Property(property: 'notes', type: 'string')], type: 'object'))), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateAttendance(): void
    {
    }

    #[OA\Delete(path: '/api/attendances/{id}', summary: 'Delete attendance', security: [['BearerAuth' => []]], tags: ['Attendances'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteAttendance(): void
    {
    }

    #[OA\Get(path: '/api/categories', summary: 'List categories', tags: ['Categories'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listCategories(): void
    {
    }

    #[OA\Post(path: '/api/categories', summary: 'Create category', tags: ['Categories'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'category_name'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'category_name', type: 'string'), new OA\Property(property: 'description', type: 'string')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createCategory(): void
    {
    }

    #[OA\Get(path: '/api/categories/{id}', summary: 'Get category', tags: ['Categories'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showCategory(): void
    {
    }

    #[OA\Put(path: '/api/categories/{id}', summary: 'Update category', tags: ['Categories'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'category_name'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'category_name', type: 'string'), new OA\Property(property: 'description', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateCategory(): void
    {
    }

    #[OA\Delete(path: '/api/categories/{id}', summary: 'Delete category', tags: ['Categories'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteCategory(): void
    {
    }

    #[OA\Get(path: '/api/products', summary: 'List products', tags: ['Products'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listProducts(): void
    {
    }

    #[OA\Post(
        path: '/api/products',
        summary: 'Create product',
        tags: ['Products'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'product_name', 'sku', 'category_id', 'unit_of_measure'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'product_name', type: 'string'), new OA\Property(property: 'sku', type: 'string'), new OA\Property(property: 'category_id', type: 'integer'), new OA\Property(property: 'description', type: 'string'), new OA\Property(property: 'unit_of_measure', type: 'string'), new OA\Property(property: 'minimum_stock', type: 'number'), new OA\Property(property: 'current_stock', type: 'number'), new OA\Property(property: 'cost_price', type: 'number'), new OA\Property(property: 'selling_price', type: 'number'), new OA\Property(property: 'is_active', type: 'boolean')], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createProduct(): void
    {
    }

    #[OA\Get(path: '/api/products/{id}', summary: 'Get product', tags: ['Products'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showProduct(): void
    {
    }

    #[OA\Put(path: '/api/products/{id}', summary: 'Update product', tags: ['Products'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateProduct(): void
    {
    }

    #[OA\Delete(path: '/api/products/{id}', summary: 'Delete product', tags: ['Products'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteProduct(): void
    {
    }

    #[OA\Get(
        path: '/api/products/{id}/recipes',
        summary: 'List recipes for a product',
        tags: ['Recipes'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function productRecipes(): void
    {
    }

    #[OA\Get(
        path: '/api/recipes',
        summary: 'List recipes',
        tags: ['Recipes'],
        parameters: [
            new OA\Parameter(name: 'product_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'ingredient_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function listRecipes(): void
    {
    }

    #[OA\Post(
        path: '/api/recipes',
        summary: 'Create recipe',
        tags: ['Recipes'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_id', 'ingredient_id', 'quantity_needed', 'unit'], properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'ingredient_id', type: 'integer'), new OA\Property(property: 'quantity_needed', type: 'number'), new OA\Property(property: 'unit', type: 'string')], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createRecipe(): void
    {
    }

    #[OA\Get(path: '/api/recipes/{id}', summary: 'Get recipe', tags: ['Recipes'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showRecipe(): void
    {
    }

    #[OA\Put(path: '/api/recipes/{id}', summary: 'Update recipe', tags: ['Recipes'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_id', 'ingredient_id', 'quantity_needed', 'unit'], properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'ingredient_id', type: 'integer'), new OA\Property(property: 'quantity_needed', type: 'number'), new OA\Property(property: 'unit', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateRecipe(): void
    {
    }

    #[OA\Delete(path: '/api/recipes/{id}', summary: 'Delete recipe', tags: ['Recipes'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteRecipe(): void
    {
    }

    #[OA\Get(path: '/api/stock-transactions', summary: 'List stock transactions', tags: ['Stock'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'product_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'employee_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'transaction_type', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'reference_type', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'from_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'to_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listStockTransactions(): void
    {
    }

    #[OA\Post(path: '/api/stock-transactions', summary: 'Create stock transaction', tags: ['Stock'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_id', 'transaction_type', 'quantity', 'reference_type'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'transaction_type', type: 'string', enum: ['in', 'out', 'adjustment']), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'reference_type', type: 'string', enum: ['purchase', 'sale', 'adjustment']), new OA\Property(property: 'reference_id', type: 'integer'), new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'notes', type: 'string'), new OA\Property(property: 'transaction_date', type: 'string', format: 'date-time')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createStockTransaction(): void
    {
    }

    #[OA\Get(path: '/api/stock-report', summary: 'Get stock report', tags: ['Stock'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'product_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'low_stock_only', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function stockReport(): void
    {
    }

    #[OA\Get(
        path: '/api/tables',
        summary: 'List QR tables',
        tags: ['Tables'],
        parameters: [
            new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['available', 'occupied', 'reserved'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function listTables(): void
    {
    }

    #[OA\Post(
        path: '/api/tables',
        summary: 'Create QR table',
        tags: ['Tables'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'table_number', 'capacity'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'table_number', type: 'string'), new OA\Property(property: 'qr_code', type: 'string'), new OA\Property(property: 'capacity', type: 'integer'), new OA\Property(property: 'status', type: 'string', enum: ['available', 'occupied', 'reserved'])], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createTable(): void
    {
    }

    #[OA\Get(path: '/api/tables/{id}', summary: 'Get QR table', tags: ['Tables'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showTable(): void
    {
    }

    #[OA\Put(path: '/api/tables/{id}', summary: 'Update QR table', tags: ['Tables'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'table_number', 'qr_code', 'capacity', 'status'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'table_number', type: 'string'), new OA\Property(property: 'qr_code', type: 'string'), new OA\Property(property: 'capacity', type: 'integer'), new OA\Property(property: 'status', type: 'string', enum: ['available', 'occupied', 'reserved'])], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateTable(): void
    {
    }

    #[OA\Delete(path: '/api/tables/{id}', summary: 'Delete QR table', tags: ['Tables'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteTable(): void
    {
    }

    #[OA\Patch(path: '/api/tables/{id}/status', summary: 'Update QR table status', tags: ['Tables'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['status'], properties: [new OA\Property(property: 'status', type: 'string', enum: ['available', 'occupied', 'reserved'])], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateTableStatus(): void
    {
    }

    #[OA\Get(
        path: '/api/pos/menu',
        summary: 'List active POS menu',
        tags: ['POS'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function listPosMenu(): void
    {
    }

    #[OA\Get(
        path: '/api/pos/tables/{qr_code}/menu',
        summary: 'List active POS menu for QR table',
        tags: ['POS'],
        parameters: [
            new OA\Parameter(name: 'qr_code', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function listPosTableMenu(): void
    {
    }

    #[OA\Post(
        path: '/api/pos/qr-orders',
        summary: 'Create QR order and Midtrans QRIS payment',
        tags: ['POS'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['qr_code', 'items'], properties: [new OA\Property(property: 'qr_code', type: 'string'), new OA\Property(property: 'customer_name', type: 'string'), new OA\Property(property: 'discount', type: 'number'), new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'notes', type: 'string')], type: 'object'))], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createQrOrder(): void
    {
    }

    #[OA\Post(
        path: '/api/pos/cashier-sessions/open',
        summary: 'Open cashier session',
        security: [['BearerAuth' => []]],
        tags: ['POS Cashier Sessions'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'opening_cash'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'opening_cash', type: 'number'), new OA\Property(property: 'opening_notes', type: 'string')], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Opened'), new OA\Response(response: 422, description: 'Already open or invalid')]
    )]
    public function openCashierSession(): void
    {
    }

    #[OA\Get(path: '/api/pos/cashier-sessions/current', summary: 'Get current open cashier session', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function currentCashierSession(): void
    {
    }

    #[OA\Get(path: '/api/pos/cashier-sessions', summary: 'List cashier sessions', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'employee_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['open', 'closed'])), new OA\Parameter(name: 'from_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'to_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listCashierSessions(): void
    {
    }

    #[OA\Get(path: '/api/pos/cashier-sessions/{id}', summary: 'Get cashier session detail', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showCashierSession(): void
    {
    }

    #[OA\Post(path: '/api/pos/cashier-sessions/{id}/cash-movements', summary: 'Create cashier cash movement', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['type', 'amount'], properties: [new OA\Property(property: 'type', type: 'string', enum: ['cash_in', 'cash_out']), new OA\Property(property: 'amount', type: 'number'), new OA\Property(property: 'category', type: 'string'), new OA\Property(property: 'description', type: 'string')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created'), new OA\Response(response: 422, description: 'Session closed')])]
    public function createCashierCashMovement(): void
    {
    }

    #[OA\Post(path: '/api/pos/cashier-sessions/{id}/close', summary: 'Close cashier session', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['closing_cash'], properties: [new OA\Property(property: 'closing_cash', type: 'number'), new OA\Property(property: 'closing_notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'Closed'), new OA\Response(response: 422, description: 'Already closed')])]
    public function closeCashierSession(): void
    {
    }

    #[OA\Get(path: '/api/pos/cashier-sessions/{id}/summary', summary: 'Get cashier session summary', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function cashierSessionSummary(): void
    {
    }

    #[OA\Get(path: '/api/pos/cashier-sessions/{id}/orders', summary: 'List cashier session orders', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function cashierSessionOrders(): void
    {
    }

    #[OA\Get(path: '/api/pos/cashier-sessions/{id}/cash-movements', summary: 'List cashier session cash movements', security: [['BearerAuth' => []]], tags: ['POS Cashier Sessions'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function cashierSessionCashMovements(): void
    {
    }

    #[OA\Post(
        path: '/api/pos/cashier-orders',
        summary: 'Create cashier order',
        tags: ['POS'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['order_type', 'store_id', 'employee_id', 'payment_method', 'items'], properties: [new OA\Property(property: 'order_type', type: 'string', enum: ['dine_in_cashier', 'takeaway']), new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'table_id', type: 'integer'), new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'customer_name', type: 'string'), new OA\Property(property: 'payment_method', type: 'string', enum: ['cash', 'qris']), new OA\Property(property: 'amount_paid', type: 'number'), new OA\Property(property: 'discount', type: 'number'), new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'notes', type: 'string')], type: 'object'))], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createCashierOrder(): void
    {
    }

    #[OA\Get(path: '/api/pos/orders', summary: 'List POS orders', tags: ['POS'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'order_status', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'payment_status', in: 'query', required: false, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listPosOrders(): void
    {
    }

    #[OA\Get(path: '/api/pos/orders/{id}', summary: 'Get POS order', tags: ['POS'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showPosOrder(): void
    {
    }

    #[OA\Patch(path: '/api/pos/orders/{id}/status', summary: 'Update POS order status', tags: ['POS'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['order_status'], properties: [new OA\Property(property: 'order_status', type: 'string', enum: ['preparing', 'ready', 'completed', 'cancelled'])], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updatePosOrderStatus(): void
    {
    }

    #[OA\Get(path: '/api/products/deleted', summary: 'List deleted products', tags: ['Products'], parameters: [new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deletedProducts(): void
    {
    }

    #[OA\Post(path: '/api/products/{id}/restore', summary: 'Restore deleted product', tags: ['Products'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function restoreProduct(): void
    {
    }

    #[OA\Delete(path: '/api/products/{id}/force', summary: 'Permanently delete product', tags: ['Products'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function forceDeleteProduct(): void
    {
    }

    #[OA\Get(path: '/api/stock-alerts', summary: 'List low stock products', tags: ['Asset Management'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function stockAlerts(): void
    {
    }

    #[OA\Get(path: '/api/stock-alerts/summary', summary: 'Get stock alert summary', tags: ['Asset Management'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function stockAlertSummary(): void
    {
    }

    #[OA\Get(path: '/api/products/{id}/stock-card', summary: 'Get product stock card', tags: ['Asset Management'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'from_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'to_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')), new OA\Parameter(name: 'transaction_type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['in', 'out', 'adjustment'])), new OA\Parameter(name: 'reference_type', in: 'query', required: false, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function productStockCard(): void
    {
    }

    #[OA\Get(path: '/api/stock-report/export', summary: 'Export stock report CSV', tags: ['Stock'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'product_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'low_stock_only', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'CSV file')])]
    public function exportStockReport(): void
    {
    }

    #[OA\Get(path: '/api/suppliers', summary: 'List suppliers', tags: ['Suppliers'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])), new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listSuppliers(): void
    {
    }

    #[OA\Post(path: '/api/suppliers', summary: 'Create supplier', tags: ['Suppliers'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'supplier_name'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'supplier_name', type: 'string'), new OA\Property(property: 'contact_name', type: 'string'), new OA\Property(property: 'phone', type: 'string'), new OA\Property(property: 'email', type: 'string', format: 'email'), new OA\Property(property: 'address', type: 'string'), new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'])], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createSupplier(): void
    {
    }

    #[OA\Get(path: '/api/suppliers/{id}', summary: 'Get supplier', tags: ['Suppliers'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showSupplier(): void
    {
    }

    #[OA\Put(path: '/api/suppliers/{id}', summary: 'Update supplier', tags: ['Suppliers'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'supplier_name', 'status'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'supplier_name', type: 'string'), new OA\Property(property: 'contact_name', type: 'string'), new OA\Property(property: 'phone', type: 'string'), new OA\Property(property: 'email', type: 'string', format: 'email'), new OA\Property(property: 'address', type: 'string'), new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'])], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateSupplier(): void
    {
    }

    #[OA\Delete(path: '/api/suppliers/{id}', summary: 'Delete supplier', tags: ['Suppliers'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteSupplier(): void
    {
    }

    #[OA\Get(path: '/api/purchase-orders', summary: 'List purchase orders', tags: ['Purchase Orders'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['draft', 'ordered', 'received', 'cancelled'])), new OA\Parameter(name: 'supplier_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listPurchaseOrders(): void
    {
    }

    #[OA\Post(path: '/api/purchase-orders', summary: 'Create purchase order', tags: ['Purchase Orders'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'order_date', 'items'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'supplier_id', type: 'integer'), new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'order_date', type: 'string', format: 'date'), new OA\Property(property: 'notes', type: 'string'), new OA\Property(property: 'items', type: 'array', items: new OA\Items(required: ['product_id', 'quantity'], properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'unit_cost', type: 'number'), new OA\Property(property: 'notes', type: 'string')], type: 'object'))], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createPurchaseOrder(): void
    {
    }

    #[OA\Get(path: '/api/purchase-orders/{id}', summary: 'Get purchase order', tags: ['Purchase Orders'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showPurchaseOrder(): void
    {
    }

    #[OA\Put(path: '/api/purchase-orders/{id}', summary: 'Update purchase order', tags: ['Purchase Orders'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'order_date', 'status'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'supplier_id', type: 'integer'), new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'order_date', type: 'string', format: 'date'), new OA\Property(property: 'status', type: 'string', enum: ['draft', 'ordered', 'cancelled']), new OA\Property(property: 'notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updatePurchaseOrder(): void
    {
    }

    #[OA\Post(path: '/api/purchase-orders/{id}/receive', summary: 'Receive purchase order', tags: ['Purchase Orders'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function receivePurchaseOrder(): void
    {
    }

    #[OA\Post(path: '/api/purchase-orders/{id}/cancel', summary: 'Cancel purchase order', tags: ['Purchase Orders'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function cancelPurchaseOrder(): void
    {
    }

    #[OA\Get(path: '/api/stock-opnames', summary: 'List stock opnames', tags: ['Stock Opnames'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listStockOpnames(): void
    {
    }

    #[OA\Post(path: '/api/stock-opnames', summary: 'Create stock opname', tags: ['Stock Opnames'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['store_id', 'opname_date'], properties: [new OA\Property(property: 'store_id', type: 'integer'), new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'opname_date', type: 'string', format: 'date'), new OA\Property(property: 'notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createStockOpname(): void
    {
    }

    #[OA\Get(path: '/api/stock-opnames/{id}', summary: 'Get stock opname', tags: ['Stock Opnames'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showStockOpname(): void
    {
    }

    #[OA\Post(path: '/api/stock-opnames/{id}/items', summary: 'Add stock opname item', tags: ['Stock Opnames'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_id', 'physical_stock'], properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'physical_stock', type: 'number'), new OA\Property(property: 'notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function addStockOpnameItem(): void
    {
    }

    #[OA\Post(path: '/api/stock-opnames/{id}/submit', summary: 'Submit stock opname', tags: ['Stock Opnames'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function submitStockOpname(): void
    {
    }

    #[OA\Post(path: '/api/stock-opnames/{id}/approve', summary: 'Approve stock opname', tags: ['Stock Opnames'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: false, content: new OA\JsonContent(properties: [new OA\Property(property: 'approved_by', type: 'integer')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function approveStockOpname(): void
    {
    }

    #[OA\Get(path: '/api/stock-adjustments', summary: 'List stock adjustments', tags: ['Stock Adjustments'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listStockAdjustments(): void
    {
    }

    #[OA\Post(path: '/api/stock-adjustments', summary: 'Create stock adjustment', tags: ['Stock Adjustments'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_id', 'quantity', 'adjustment_type'], properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'adjustment_type', type: 'string', enum: ['increase', 'decrease']), new OA\Property(property: 'requested_by', type: 'integer'), new OA\Property(property: 'reason', type: 'string')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createStockAdjustment(): void
    {
    }

    #[OA\Post(path: '/api/stock-adjustments/{id}/approve', summary: 'Approve stock adjustment', tags: ['Stock Adjustments'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: false, content: new OA\JsonContent(properties: [new OA\Property(property: 'approved_by', type: 'integer'), new OA\Property(property: 'approval_notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function approveStockAdjustment(): void
    {
    }

    #[OA\Post(path: '/api/stock-adjustments/{id}/reject', summary: 'Reject stock adjustment', tags: ['Stock Adjustments'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: false, content: new OA\JsonContent(properties: [new OA\Property(property: 'approved_by', type: 'integer'), new OA\Property(property: 'approval_notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function rejectStockAdjustment(): void
    {
    }

    #[OA\Get(path: '/api/product-batches', summary: 'List product batches', tags: ['Product Batches'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'product_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listProductBatches(): void
    {
    }

    #[OA\Post(path: '/api/product-batches', summary: 'Create product batch', tags: ['Product Batches'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_id', 'batch_number', 'quantity'], properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'batch_number', type: 'string'), new OA\Property(property: 'expired_date', type: 'string', format: 'date'), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'received_date', type: 'string', format: 'date'), new OA\Property(property: 'notes', type: 'string')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createProductBatch(): void
    {
    }

    #[OA\Get(path: '/api/product-batches/expiring-soon', summary: 'List expiring product batches', tags: ['Product Batches'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'days', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function expiringProductBatches(): void
    {
    }

    #[OA\Get(path: '/api/assets/summary', summary: 'Get asset summary', tags: ['Asset Management'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function assetSummary(): void
    {
    }

    #[OA\Get(path: '/api/assets/low-stock-summary', summary: 'Get low stock summary', tags: ['Asset Management'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function assetLowStockSummary(): void
    {
    }

    #[OA\Get(path: '/api/assets/stock-movement-summary', summary: 'Get stock movement summary', tags: ['Asset Management'], parameters: [new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function assetStockMovementSummary(): void
    {
    }

    #[OA\Get(
        path: '/api/revenue/summary',
        summary: 'Get revenue summary with daily details',
        tags: ['Revenue Reports'],
        parameters: [
            new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'from_date', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to_date', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'payment_method', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['cash', 'qris'])),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function revenueSummary(): void
    {
    }

    #[OA\Get(
        path: '/api/revenue/daily',
        summary: 'Get daily revenue',
        tags: ['Revenue Reports'],
        parameters: [
            new OA\Parameter(name: 'store_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'payment_method', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['cash', 'qris'])),
            new OA\Parameter(name: 'include_orders', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function dailyRevenue(): void
    {
    }

    #[OA\Post(path: '/api/payments/midtrans/webhook', summary: 'Midtrans payment webhook', tags: ['Payments'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function midtransWebhook(): void
    {
    }

    #[OA\Post(
        path: '/api/payments/bni-qris/webhook',
        summary: 'BNI QRIS payment webhook',
        tags: ['Payments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function bniQrisWebhook(): void
    {
    }

    #[OA\Post(
        path: '/api/dev/bni-qris/create-test',
        summary: 'Create test BNI QRIS transaction',
        security: [['BearerAuth' => []]],
        tags: ['Payments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount'],
                properties: [
                    new OA\Property(property: 'order_id', type: 'string', example: 'DEV-BNI-0001'),
                    new OA\Property(property: 'amount', type: 'number', example: 10000),
                    new OA\Property(property: 'customer_name', type: 'string', example: 'Customer Test'),
                    new OA\Property(property: 'description', type: 'string', example: 'Test QRIS BNI'),
                    new OA\Property(property: 'currency', type: 'string', example: 'IDR'),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function bniQrisCreateTest(): void
    {
    }
}
