import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { AuthProvider } from "@/contexts/AuthContext";
import ProtectedRoute from "@/components/ProtectedRoute";
import AppLayout from "@/components/AppLayout";
import SignIn from "@/pages/SignIn";
import Dashboard from "@/pages/Dashboard";
import PagePlaceholder from "@/components/PagePlaceholder";

const queryClient = new QueryClient();

function PlaceholderPage({ title }: { title: string }) {
  return <PagePlaceholder title={title} />;
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/sign-in" element={<SignIn />} />
            <Route path="/" element={<ProtectedRoute><AppLayout /></ProtectedRoute>}>
              <Route index element={<Dashboard />} />
              {/* Diocese */}
              <Route path="diocese/register" element={<PlaceholderPage title="Register Diocese" />} />
              <Route path="diocese/manage" element={<PlaceholderPage title="Manage Dioceses" />} />
              <Route path="diocese/create-admin" element={<PlaceholderPage title="Create Diocese Admin" />} />
              <Route path="diocese/admins" element={<PlaceholderPage title="Diocese Admins List" />} />
              {/* Provinces */}
              <Route path="provinces/register" element={<PlaceholderPage title="Register Province" />} />
              <Route path="provinces/manage" element={<PlaceholderPage title="Manage Provinces" />} />
              {/* Head Parishes */}
              <Route path="head-parishes/register" element={<PlaceholderPage title="Register Head Parish" />} />
              <Route path="head-parishes/manage" element={<PlaceholderPage title="Manage Head Parishes" />} />
              {/* Banks */}
              <Route path="banks/register" element={<PlaceholderPage title="Register Bank" />} />
              <Route path="banks/manage" element={<PlaceholderPage title="Manage Banks" />} />
              {/* Locations */}
              <Route path="locations/register-regions" element={<PlaceholderPage title="Register Regions" />} />
              <Route path="locations/manage-regions" element={<PlaceholderPage title="Manage Regions" />} />
              <Route path="locations/register-districts" element={<PlaceholderPage title="Register Districts" />} />
              <Route path="locations/manage-districts" element={<PlaceholderPage title="Manage Districts" />} />
              {/* Data */}
              <Route path="data/add-titles" element={<PlaceholderPage title="Add Titles" />} />
              <Route path="data/manage-titles" element={<PlaceholderPage title="Manage Titles" />} />
              <Route path="data/add-occupations" element={<PlaceholderPage title="Add Occupations" />} />
              <Route path="data/manage-occupations" element={<PlaceholderPage title="Manage Occupations" />} />
              <Route path="data/register-praise-song" element={<PlaceholderPage title="Register Praise Song" />} />
              <Route path="data/manage-praise-songs" element={<PlaceholderPage title="Manage Praise Songs" />} />
              {/* Payments */}
              <Route path="payments/manage" element={<PlaceholderPage title="Manage Payments" />} />
              <Route path="payments/reports" element={<PlaceholderPage title="Payment Reports" />} />
              {/* Reports */}
              <Route path="reports/sales" element={<PlaceholderPage title="Sales Report" />} />
              <Route path="reports/sms-usage" element={<PlaceholderPage title="SMS Usage Report" />} />
              {/* Catch-all */}
              <Route path="*" element={<Navigate to="/" replace />} />
            </Route>
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  );
}
