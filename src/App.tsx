import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { AuthProvider } from "@/contexts/AuthContext";
import ProtectedRoute from "@/components/ProtectedRoute";
import AppLayout from "@/components/AppLayout";
import SignIn from "@/pages/SignIn";
import Dashboard from "@/pages/Dashboard";
import PagePlaceholder from "@/components/PagePlaceholder";

const queryClient = new QueryClient();

function P({ title }: { title: string }) {
  return <PagePlaceholder title={title} />;
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/sign-in" element={<SignIn />} />
            <Route
              path="/"
              element={
                <ProtectedRoute>
                  <AppLayout />
                </ProtectedRoute>
              }
            >
              <Route index element={<Dashboard />} />
              <Route path="diocese/register" element={<P title="Register Diocese" />} />
              <Route path="diocese/manage" element={<P title="Manage Dioceses" />} />
              <Route path="diocese/create-admin" element={<P title="Create Diocese Admin" />} />
              <Route path="diocese/admins" element={<P title="Diocese Admins List" />} />
              <Route path="provinces/register" element={<P title="Register Province" />} />
              <Route path="provinces/manage" element={<P title="Manage Provinces" />} />
              <Route path="head-parishes/register" element={<P title="Register Head Parish" />} />
              <Route path="head-parishes/manage" element={<P title="Manage Head Parishes" />} />
              <Route path="banks/register" element={<P title="Register Bank" />} />
              <Route path="banks/manage" element={<P title="Manage Banks" />} />
              <Route path="locations/register-regions" element={<P title="Register Regions" />} />
              <Route path="locations/manage-regions" element={<P title="Manage Regions" />} />
              <Route path="locations/register-districts" element={<P title="Register Districts" />} />
              <Route path="locations/manage-districts" element={<P title="Manage Districts" />} />
              <Route path="data/add-titles" element={<P title="Add Titles" />} />
              <Route path="data/manage-titles" element={<P title="Manage Titles" />} />
              <Route path="data/add-occupations" element={<P title="Add Occupations" />} />
              <Route path="data/manage-occupations" element={<P title="Manage Occupations" />} />
              <Route path="data/register-praise-song" element={<P title="Register Praise Song" />} />
              <Route path="data/manage-praise-songs" element={<P title="Manage Praise Songs" />} />
              <Route path="payments/manage" element={<P title="Manage Payments" />} />
              <Route path="payments/reports" element={<P title="Payment Reports" />} />
              <Route path="reports/sales" element={<P title="Sales Report" />} />
              <Route path="reports/sms-usage" element={<P title="SMS Usage Report" />} />
              <Route path="*" element={<Navigate to="/" replace />} />
            </Route>
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  );
}
