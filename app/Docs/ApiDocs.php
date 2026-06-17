<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'Calon Mantu API', version: 'v1', description: 'API documentation for Calon Mantu')]
#[OA\Server(url: '/', description: 'Default Server')]
#[OA\SecurityScheme(securityScheme: 'BearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'JWT')]
class ApiDocs
{
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

    #[OA\Get(path: '/api/employees', summary: 'List employees', tags: ['Employees'], responses: [new OA\Response(response: 200, description: 'OK')])]
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
                    required: ['full_name', 'email', 'join_date', 'role_id', 'username', 'password', 'password_confirmation', 'ktp', 'kk'],
                    properties: [
                        new OA\Property(property: 'full_name', type: 'string'),
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

    #[OA\Get(path: '/api/categories', summary: 'List categories', tags: ['Categories'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listCategories(): void
    {
    }

    #[OA\Post(path: '/api/categories', summary: 'Create category', tags: ['Categories'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['category_name'], properties: [new OA\Property(property: 'category_name', type: 'string'), new OA\Property(property: 'description', type: 'string')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createCategory(): void
    {
    }

    #[OA\Get(path: '/api/categories/{id}', summary: 'Get category', tags: ['Categories'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function showCategory(): void
    {
    }

    #[OA\Put(path: '/api/categories/{id}', summary: 'Update category', tags: ['Categories'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['category_name'], properties: [new OA\Property(property: 'category_name', type: 'string'), new OA\Property(property: 'description', type: 'string')], type: 'object')), responses: [new OA\Response(response: 200, description: 'OK')])]
    public function updateCategory(): void
    {
    }

    #[OA\Delete(path: '/api/categories/{id}', summary: 'Delete category', tags: ['Categories'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function deleteCategory(): void
    {
    }

    #[OA\Get(path: '/api/products', summary: 'List products', tags: ['Products'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listProducts(): void
    {
    }

    #[OA\Post(
        path: '/api/products',
        summary: 'Create product',
        tags: ['Products'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_name', 'sku', 'category_id', 'unit_of_measure'], properties: [new OA\Property(property: 'product_name', type: 'string'), new OA\Property(property: 'sku', type: 'string'), new OA\Property(property: 'category_id', type: 'integer'), new OA\Property(property: 'description', type: 'string'), new OA\Property(property: 'unit_of_measure', type: 'string'), new OA\Property(property: 'minimum_stock', type: 'number'), new OA\Property(property: 'current_stock', type: 'number'), new OA\Property(property: 'cost_price', type: 'number'), new OA\Property(property: 'selling_price', type: 'number'), new OA\Property(property: 'is_active', type: 'boolean')], type: 'object')),
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

    #[OA\Get(path: '/api/stock-transactions', summary: 'List stock transactions', tags: ['Stock'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function listStockTransactions(): void
    {
    }

    #[OA\Post(path: '/api/stock-transactions', summary: 'Create stock transaction', tags: ['Stock'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['product_id', 'transaction_type', 'quantity', 'reference_type'], properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'transaction_type', type: 'string', enum: ['in', 'out', 'adjustment']), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'reference_type', type: 'string', enum: ['purchase', 'sale', 'adjustment']), new OA\Property(property: 'reference_id', type: 'integer'), new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'notes', type: 'string'), new OA\Property(property: 'transaction_date', type: 'string', format: 'date-time')], type: 'object')), responses: [new OA\Response(response: 201, description: 'Created')])]
    public function createStockTransaction(): void
    {
    }

    #[OA\Get(path: '/api/stock-report', summary: 'Get stock report', tags: ['Stock'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function stockReport(): void
    {
    }

    #[OA\Get(
        path: '/api/pos/menu',
        summary: 'List active POS menu',
        tags: ['POS'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
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
        path: '/api/pos/cashier-orders',
        summary: 'Create cashier order',
        tags: ['POS'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['order_type', 'employee_id', 'payment_method', 'items'], properties: [new OA\Property(property: 'order_type', type: 'string', enum: ['dine_in_cashier', 'takeaway']), new OA\Property(property: 'table_id', type: 'integer'), new OA\Property(property: 'employee_id', type: 'integer'), new OA\Property(property: 'customer_name', type: 'string'), new OA\Property(property: 'payment_method', type: 'string', enum: ['cash', 'qris']), new OA\Property(property: 'amount_paid', type: 'number'), new OA\Property(property: 'discount', type: 'number'), new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [new OA\Property(property: 'product_id', type: 'integer'), new OA\Property(property: 'quantity', type: 'number'), new OA\Property(property: 'notes', type: 'string')], type: 'object'))], type: 'object')),
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function createCashierOrder(): void
    {
    }

    #[OA\Get(path: '/api/pos/orders', summary: 'List POS orders', tags: ['POS'], responses: [new OA\Response(response: 200, description: 'OK')])]
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

    #[OA\Post(path: '/api/payments/midtrans/webhook', summary: 'Midtrans payment webhook', tags: ['Payments'], responses: [new OA\Response(response: 200, description: 'OK')])]
    public function midtransWebhook(): void
    {
    }
}
