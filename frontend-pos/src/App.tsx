import { Navigate, RouterProvider, createBrowserRouter } from 'react-router-dom';
import { AppLayout } from './layouts/AppLayout';
import { LoginPage } from './pages/LoginPage';
import { DashboardPage } from './pages/DashboardPage';
import { PosPage } from './pages/PosPage';
import { OrdersPage } from './pages/OrdersPage';
import { ProductsPage } from './pages/ProductsPage';
import { CategoriesPage } from './pages/CategoriesPage';
import { StockPage } from './pages/StockPage';
import { EmployeesPage } from './pages/EmployeesPage';
import { UserOrderPage } from './pages/UserOrderPage';
import { useAuth } from './lib/auth';

function ProtectedRoute() {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? <AppLayout /> : <Navigate to="/login" replace />;
}

const router = createBrowserRouter([
  { path: '/login', element: <LoginPage /> },
  { path: '/u', element: <UserOrderPage /> },
  { path: '/u/:qrCode', element: <UserOrderPage /> },
  { path: '/order', element: <UserOrderPage /> },
  { path: '/order/:qrCode', element: <UserOrderPage /> },
  {
    path: '/',
    element: <ProtectedRoute />,
    children: [
      { index: true, element: <DashboardPage /> },
      { path: 'pos', element: <PosPage /> },
      { path: 'orders', element: <OrdersPage /> },
      { path: 'products', element: <ProductsPage /> },
      { path: 'categories', element: <CategoriesPage /> },
      { path: 'stock', element: <StockPage /> },
      { path: 'employees', element: <EmployeesPage /> },
    ],
  },
]);

export function App() {
  return <RouterProvider router={router} />;
}
