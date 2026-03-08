import { BrowserRouter, Routes, Route } from "react-router-dom";
import Landing from "./pages/Landing";
import ChurchPage from "./pages/ChurchPage";
import TermsPage from "./pages/TermsPage";
import PrivacyPage from "./pages/PrivacyPage";
import CookiesPage from "./pages/CookiesPage";
import CookieConsent from "./components/CookieConsent";

// ELCT Sign-In Pages
import DioceseSignIn from "./pages/diocese/SignIn";
import ProvinceSignIn from "./pages/province/SignIn";
import SubParishSignIn from "./pages/sub-parish/SignIn";
import CommunitySignIn from "./pages/community/SignIn";

// ELCT Head Parish Admin
import HeadParishLayout from "./components/head-parish/HeadParishLayout";
import HeadParishSignIn from "./pages/head-parish/SignIn";
import Dashboard from "./pages/head-parish/Dashboard";
import SubParishes from "./pages/head-parish/SubParishes";
import RegisterSubParish from "./pages/head-parish/RegisterSubParish";
import Communities from "./pages/head-parish/Communities";
import RegisterCommunity from "./pages/head-parish/RegisterCommunity";
import Groups from "./pages/head-parish/Groups";
import RegisterGroup from "./pages/head-parish/RegisterGroup";
import CreateAdmin from "./pages/head-parish/CreateAdmin";
import ChurchLeaders from "./pages/head-parish/ChurchLeaders";
import RegisterChurchLeader from "./pages/head-parish/RegisterChurchLeader";
import ChurchMembers from "./pages/head-parish/ChurchMembers";
import RegisterChurchMember from "./pages/head-parish/RegisterChurchMember";
import ChurchChoirs from "./pages/head-parish/ChurchChoirs";
import RegisterChurchChoir from "./pages/head-parish/RegisterChurchChoir";
import SundayServices from "./pages/head-parish/SundayServices";
import RecordSundayService from "./pages/head-parish/RecordSundayService";
import BankAccounts from "./pages/head-parish/BankAccounts";
import RegisterBankAccount from "./pages/head-parish/RegisterBankAccount";
import FinancialStatement from "./pages/head-parish/FinancialStatement";
import RevenueStreams from "./pages/head-parish/RevenueStreams";
import RecordRevenue from "./pages/head-parish/RecordRevenue";
import Debits from "./pages/head-parish/Debits";
import Harambee from "./pages/head-parish/Harambee";
import RecordHarambee from "./pages/head-parish/RecordHarambee";
import ManageEnvelopes from "./pages/head-parish/ManageEnvelopes";
import AllMeetings from "./pages/head-parish/AllMeetings";
import NewMeeting from "./pages/head-parish/NewMeeting";
import ChurchEvents from "./pages/head-parish/ChurchEvents";
import NewChurchEvent from "./pages/head-parish/NewChurchEvent";
import ExpenseRequests from "./pages/head-parish/ExpenseRequests";
import MakeExpenseRequest from "./pages/head-parish/MakeExpenseRequest";
import ExcludedChurchMembers from "./pages/head-parish/ExcludedChurchMembers";
import PaymentGatewayWallets from "./pages/head-parish/PaymentGatewayWallets";
import AssetsManagement from "./pages/head-parish/AssetsManagement";
import SendPushNotification from "./pages/head-parish/SendPushNotification";
import GenericPage from "./pages/head-parish/GenericPage";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Landing />} />
        <Route path="/churches/:slug" element={<ChurchPage />} />
        <Route path="/terms" element={<TermsPage />} />
        <Route path="/privacy" element={<PrivacyPage />} />
        <Route path="/cookies" element={<CookiesPage />} />

        {/* ELCT Sign-In Pages */}
        <Route path="/elct/diocese/sign-in" element={<DioceseSignIn />} />
        <Route path="/elct/province/sign-in" element={<ProvinceSignIn />} />
        <Route path="/elct/sub-parish/sign-in" element={<SubParishSignIn />} />
        <Route path="/elct/community/sign-in" element={<CommunitySignIn />} />

        {/* ELCT Head Parish */}
        <Route path="/elct/head-parish" element={<HeadParishLayout />}>
          <Route index element={<Dashboard />} />
          {/* Administration */}
          <Route path="sub-parishes" element={<SubParishes />} />
          <Route path="register-sub-parish" element={<RegisterSubParish />} />
          <Route path="communities" element={<Communities />} />
          <Route path="register-community" element={<RegisterCommunity />} />
          <Route path="groups" element={<Groups />} />
          <Route path="register-group" element={<RegisterGroup />} />
          {/* Roles */}
          <Route path="create-admin" element={<CreateAdmin />} />
          {/* Church Management */}
          <Route path="church-leaders" element={<ChurchLeaders />} />
          <Route path="register-church-leader" element={<RegisterChurchLeader />} />
          <Route path="church-members" element={<ChurchMembers />} />
          <Route path="register-church-member" element={<RegisterChurchMember />} />
          <Route path="church-choirs" element={<ChurchChoirs />} />
          <Route path="register-church-choir" element={<RegisterChurchChoir />} />
          <Route path="sunday-services" element={<SundayServices />} />
          <Route path="record-sunday-service" element={<RecordSundayService />} />
          <Route path="excluded-church-members" element={<ExcludedChurchMembers />} />
          {/* Events */}
          <Route path="all-meetings" element={<AllMeetings />} />
          <Route path="new-meeting" element={<NewMeeting />} />
          <Route path="church-events" element={<ChurchEvents />} />
          <Route path="new-church-event" element={<NewChurchEvent />} />
          <Route path="send-push-notification" element={<SendPushNotification />} />
          {/* Banking & Finance */}
          <Route path="bank-accounts" element={<BankAccounts />} />
          <Route path="register-bank-account" element={<RegisterBankAccount />} />
          <Route path="financial-statement" element={<FinancialStatement />} />
          {/* Revenue */}
          <Route path="revenue-streams" element={<RevenueStreams />} />
          <Route path="record-revenue" element={<RecordRevenue />} />
          <Route path="debits" element={<Debits />} />
          {/* Expenses */}
          <Route path="expense-requests" element={<ExpenseRequests />} />
          <Route path="make-expense-request" element={<MakeExpenseRequest />} />
          {/* Harambee */}
          <Route path="harambee" element={<Harambee />} />
          <Route path="record-harambee" element={<RecordHarambee />} />
          {/* Envelope */}
          <Route path="manage-envelopes" element={<ManageEnvelopes />} />
          {/* Assets */}
          <Route path="add-asset" element={<AssetsManagement />} />
          {/* Payment Wallets */}
          <Route path="payment-gateway-wallets" element={<PaymentGatewayWallets />} />
          {/* Catch-all */}
          <Route path="*" element={<GenericPage />} />
        </Route>
      </Routes>
      <CookieConsent />
    </BrowserRouter>
  );
}
