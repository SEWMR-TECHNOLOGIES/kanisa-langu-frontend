import { BrowserRouter, Routes, Route } from "react-router-dom";
import Landing from "./pages/Landing";
import ChurchPage from "./pages/ChurchPage";
import TermsPage from "./pages/TermsPage";
import PrivacyPage from "./pages/PrivacyPage";
import CookiesPage from "./pages/CookiesPage";
import CookieConsent from "./components/CookieConsent";
import AutoPage from "./components/head-parish/AutoPage";
import { headParishPages, subParishPages } from "./data/pageConfigs";

// ELCT Sign-In Pages
import DioceseSignIn from "./pages/diocese/SignIn";
import ProvinceSignIn from "./pages/province/SignIn";
import HeadParishSignIn from "./pages/head-parish/SignIn";
import SubParishSignIn from "./pages/sub-parish/SignIn";
import CommunitySignIn from "./pages/community/SignIn";

// ELCT Diocese Admin
import DioceseLayout from "./pages/diocese/DioceseLayout";
import DioceseDashboard from "./pages/diocese/Dashboard";
import DioceseProvinces from "./pages/diocese/Provinces";
import DioceseProvinceAdmins from "./pages/diocese/ProvinceAdmins";
import DioceseCreateProvinceAdmin from "./pages/diocese/CreateProvinceAdmin";
import DioceseAdmins from "./pages/diocese/Admins";
import DioceseOverview from "./pages/diocese/Overview";

// ELCT Province Admin
import ProvinceLayout from "./pages/province/ProvinceLayout";
import ProvinceDashboard from "./pages/province/Dashboard";
import ProvinceHeadParishes from "./pages/province/HeadParishes";
import ProvinceCreateHPAdmin from "./pages/province/CreateHPAdmin";
import ProvinceHPAdmins from "./pages/province/HPAdmins";
import ProvinceAdmins from "./pages/province/Admins";
import ProvinceOverview from "./pages/province/Overview";
import ProvinceFinancialSummary from "./pages/province/FinancialSummary";
import ProvinceMembersOverview from "./pages/province/MembersOverview";

// ELCT Head Parish Admin
import HeadParishLayout from "./components/head-parish/HeadParishLayout";
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
import GroupedRequests from "./pages/head-parish/GroupedRequests";
import ExcludedChurchMembers from "./pages/head-parish/ExcludedChurchMembers";
import PaymentGatewayWallets from "./pages/head-parish/PaymentGatewayWallets";
import AssetsManagement from "./pages/head-parish/AssetsManagement";
import SendPushNotification from "./pages/head-parish/SendPushNotification";
import RecordHarambeeContribution from "./pages/head-parish/RecordHarambeeContribution";
import UploadChurchMembers from "./pages/head-parish/UploadChurchMembers";
import UploadEnvelopeData from "./pages/head-parish/UploadEnvelopeData";
import UploadHarambeeTargets from "./pages/head-parish/UploadHarambeeTargets";
import HPProfile from "./pages/head-parish/Profile";

// ELCT Sub Parish Admin
import SubParishLayout from "./pages/sub-parish/SubParishLayout";
import SubParishDashboard from "./pages/sub-parish/Dashboard";
import SPChurchMembers from "./pages/sub-parish/ChurchMembers";
import SPRegisterMember from "./pages/sub-parish/RegisterChurchMember";
import SPServices from "./pages/sub-parish/Services";
import SPMeetings from "./pages/sub-parish/Meetings";
import SPRevenueStreams from "./pages/sub-parish/RevenueStreams";
import SPHarambee from "./pages/sub-parish/Harambee";
import SPManageEnvelopes from "./pages/sub-parish/ManageEnvelopes";
import SPExpenseRequests from "./pages/sub-parish/ExpenseRequests";
import SPCreateAdmin from "./pages/sub-parish/CreateAdmin";

// ELCT Community Admin
import CommunityLayout from "./pages/community/CommunityLayout";
import CommunityDashboard from "./pages/community/Dashboard";
import CommunityMembersPage from "./pages/community/Members";
import CommunityHouseholds from "./pages/community/Households";
import CommunityScheduleMeeting from "./pages/community/ScheduleMeeting";
import CommunityMeetings from "./pages/community/Meetings";
import CommunitySendNotification from "./pages/community/SendNotification";

// Super Admin
import SuperAdminLayout from "./pages/super-admin/SuperAdminLayout";
import SuperAdminDashboard from "./pages/super-admin/Dashboard";
import SuperAdminSignIn from "./pages/super-admin/SignIn";
import SARegisterDiocese from "./pages/super-admin/RegisterDiocese";
import SAManageDioceses from "./pages/super-admin/ManageDioceses";
import SACreateDioceseAdmin from "./pages/super-admin/CreateDioceseAdmin";
import SADioceseAdminsList from "./pages/super-admin/DioceseAdminsList";
import SARegisterProvince from "./pages/super-admin/RegisterProvince";
import SAManageProvinces from "./pages/super-admin/ManageProvinces";
import SARegisterHeadParish from "./pages/super-admin/RegisterHeadParish";
import SAManageHeadParishes from "./pages/super-admin/ManageHeadParishes";
import SARegisterBank from "./pages/super-admin/RegisterBank";
import SAManageBanks from "./pages/super-admin/ManageBanks";
import SARegisterRegion from "./pages/super-admin/RegisterRegion";
import SAManageRegions from "./pages/super-admin/ManageRegions";
import SARegisterDistrict from "./pages/super-admin/RegisterDistrict";
import SAManageDistricts from "./pages/super-admin/ManageDistricts";
import SAAddTitle from "./pages/super-admin/AddTitle";
import SAManageTitles from "./pages/super-admin/ManageTitles";
import SAAddOccupation from "./pages/super-admin/AddOccupation";
import SAManageOccupations from "./pages/super-admin/ManageOccupations";
import SARegisterPraiseSong from "./pages/super-admin/RegisterPraiseSong";
import SAManagePraiseSongs from "./pages/super-admin/ManagePraiseSongs";
import SAProfile from "./pages/super-admin/Profile";

function HPAutoPage() {
  return <AutoPage configs={headParishPages} />;
}

function SPAutoPage() {
  return <AutoPage configs={subParishPages} />;
}

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
        <Route path="/elct/head-parish/sign-in" element={<HeadParishSignIn />} />
        <Route path="/elct/sub-parish/sign-in" element={<SubParishSignIn />} />
        <Route path="/elct/community/sign-in" element={<CommunitySignIn />} />

        {/* ELCT Diocese */}
        <Route path="/elct/diocese" element={<DioceseLayout />}>
          <Route index element={<DioceseDashboard />} />
          <Route path="provinces" element={<DioceseProvinces />} />
          <Route path="province-admins" element={<DioceseProvinceAdmins />} />
          <Route path="create-province-admin" element={<DioceseCreateProvinceAdmin />} />
          <Route path="admins" element={<DioceseAdmins />} />
          <Route path="overview" element={<DioceseOverview />} />
        </Route>

        {/* ELCT Province */}
        <Route path="/elct/province" element={<ProvinceLayout />}>
          <Route index element={<ProvinceDashboard />} />
          <Route path="head-parishes" element={<ProvinceHeadParishes />} />
          <Route path="create-hp-admin" element={<ProvinceCreateHPAdmin />} />
          <Route path="hp-admins" element={<ProvinceHPAdmins />} />
          <Route path="admins" element={<ProvinceAdmins />} />
          <Route path="overview" element={<ProvinceOverview />} />
          <Route path="financial-summary" element={<ProvinceFinancialSummary />} />
          <Route path="members-overview" element={<ProvinceMembersOverview />} />
        </Route>

        {/* ELCT Head Parish */}
        <Route path="/elct/head-parish" element={<HeadParishLayout />}>
          <Route index element={<Dashboard />} />
          <Route path="sub-parishes" element={<SubParishes />} />
          <Route path="register-sub-parish" element={<RegisterSubParish />} />
          <Route path="communities" element={<Communities />} />
          <Route path="register-community" element={<RegisterCommunity />} />
          <Route path="groups" element={<Groups />} />
          <Route path="register-group" element={<RegisterGroup />} />
          <Route path="create-admin" element={<CreateAdmin />} />
          <Route path="church-leaders" element={<ChurchLeaders />} />
          <Route path="register-church-leader" element={<RegisterChurchLeader />} />
          <Route path="church-members" element={<ChurchMembers />} />
          <Route path="register-church-member" element={<RegisterChurchMember />} />
          <Route path="church-choirs" element={<ChurchChoirs />} />
          <Route path="register-church-choir" element={<RegisterChurchChoir />} />
          <Route path="sunday-services" element={<SundayServices />} />
          <Route path="record-sunday-service" element={<RecordSundayService />} />
          <Route path="excluded-church-members" element={<ExcludedChurchMembers />} />
          <Route path="all-meetings" element={<AllMeetings />} />
          <Route path="new-meeting" element={<NewMeeting />} />
          <Route path="church-events" element={<ChurchEvents />} />
          <Route path="new-church-event" element={<NewChurchEvent />} />
          <Route path="send-push-notification" element={<SendPushNotification />} />
          <Route path="bank-accounts" element={<BankAccounts />} />
          <Route path="register-bank-account" element={<RegisterBankAccount />} />
          <Route path="financial-statement" element={<FinancialStatement />} />
          <Route path="revenue-streams" element={<RevenueStreams />} />
          <Route path="record-revenue" element={<RecordRevenue />} />
          <Route path="debits" element={<Debits />} />
          <Route path="expense-requests" element={<ExpenseRequests />} />
          <Route path="make-expense-request" element={<MakeExpenseRequest />} />
          <Route path="grouped-requests" element={<GroupedRequests />} />
          <Route path="harambee" element={<Harambee />} />
          <Route path="record-harambee" element={<RecordHarambee />} />
          <Route path="record-harambee-contribution" element={<RecordHarambeeContribution />} />
          <Route path="upload-church-members" element={<UploadChurchMembers />} />
          <Route path="upload-envelope-data" element={<UploadEnvelopeData />} />
          <Route path="upload-harambee-targets" element={<UploadHarambeeTargets />} />
          <Route path="manage-envelopes" element={<ManageEnvelopes />} />
          <Route path="add-asset" element={<AssetsManagement />} />
          <Route path="payment-gateway-wallets" element={<PaymentGatewayWallets />} />
          <Route path="profile" element={<HPProfile />} />
          {/* All remaining HP pages rendered via AutoPage */}
          <Route path="*" element={<HPAutoPage />} />
        </Route>

        {/* ELCT Sub Parish */}
        <Route path="/elct/sub-parish" element={<SubParishLayout />}>
          <Route index element={<SubParishDashboard />} />
          <Route path="create-admin" element={<SPCreateAdmin />} />
          <Route path="register-church-member" element={<SPRegisterMember />} />
          <Route path="church-members" element={<SPChurchMembers />} />
          <Route path="services" element={<SPServices />} />
          <Route path="meetings" element={<SPMeetings />} />
          <Route path="revenue-streams" element={<SPRevenueStreams />} />
          <Route path="harambee" element={<SPHarambee />} />
          <Route path="manage-envelopes" element={<SPManageEnvelopes />} />
          <Route path="expense-requests" element={<SPExpenseRequests />} />
          {/* All remaining SP pages via AutoPage */}
          <Route path="*" element={<SPAutoPage />} />
        </Route>

        {/* ELCT Community */}
        <Route path="/elct/community" element={<CommunityLayout />}>
          <Route index element={<CommunityDashboard />} />
          <Route path="members" element={<CommunityMembersPage />} />
          <Route path="households" element={<CommunityHouseholds />} />
          <Route path="schedule-meeting" element={<CommunityScheduleMeeting />} />
          <Route path="meetings" element={<CommunityMeetings />} />
          <Route path="send-notification" element={<CommunitySendNotification />} />
        </Route>

        {/* Super Admin */}
        <Route path="/app/sign-in" element={<SuperAdminSignIn />} />
        <Route path="/app" element={<SuperAdminLayout />}>
          <Route index element={<SuperAdminDashboard />} />
          <Route path="register-diocese" element={<SARegisterDiocese />} />
          <Route path="manage-dioceses" element={<SAManageDioceses />} />
          <Route path="create-diocese-admin" element={<SACreateDioceseAdmin />} />
          <Route path="diocese-admins-list" element={<SADioceseAdminsList />} />
          <Route path="register-province" element={<SARegisterProvince />} />
          <Route path="manage-provinces" element={<SAManageProvinces />} />
          <Route path="register-head-parish" element={<SARegisterHeadParish />} />
          <Route path="manage-head-parishes" element={<SAManageHeadParishes />} />
          <Route path="register-bank" element={<SARegisterBank />} />
          <Route path="manage-banks" element={<SAManageBanks />} />
          <Route path="register-region" element={<SARegisterRegion />} />
          <Route path="manage-regions" element={<SAManageRegions />} />
          <Route path="register-district" element={<SARegisterDistrict />} />
          <Route path="manage-districts" element={<SAManageDistricts />} />
          <Route path="add-title" element={<SAAddTitle />} />
          <Route path="manage-titles" element={<SAManageTitles />} />
          <Route path="add-occupation" element={<SAAddOccupation />} />
          <Route path="manage-occupations" element={<SAManageOccupations />} />
          <Route path="register-praise-song" element={<SARegisterPraiseSong />} />
          <Route path="manage-praise-songs" element={<SAManagePraiseSongs />} />
          <Route path="profile" element={<SAProfile />} />
        </Route>
      </Routes>
      <CookieConsent />
    </BrowserRouter>
  );
}
