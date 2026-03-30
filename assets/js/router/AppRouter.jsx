import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import MainLayout from '../components/layout/MainLayout';
import LoginForm from '../components/auth/LoginForm';
import Dashboard from '../components/Dashboard';
import ClientList from '../components/clients/ClientList';
import ClientForm from '../components/clients/ClientForm';
import ClientDetail from '../components/clients/ClientDetail';
import DealList from '../components/deals/DealList';
import DealForm from '../components/deals/DealForm';
import DealDetail from '../components/deals/DealDetail';
import DealPipeline from '../components/deals/DealPipeline';
import NoteList from '../components/notes/NoteList';
import CalendarView from '../components/calendar/CalendarView';
import EmailInbox from '../components/email/EmailInbox';
import PanelNavigation from '../components/panel/PanelNavigation';
import UserManagement from '../components/settings/UserManagement';
import EmailAccounts from '../components/settings/EmailAccounts';
import EmailTemplates from '../components/settings/EmailTemplates';

export default function AppRouter() {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/login" element={<LoginForm />} />
                <Route element={<MainLayout />}>
                    <Route path="/" element={<Dashboard />} />
                    <Route path="/clients" element={<ClientList />} />
                    <Route path="/clients/new" element={<ClientForm />} />
                    <Route path="/clients/:id" element={<ClientDetail />} />
                    <Route path="/clients/:id/edit" element={<ClientForm />} />
                    <Route path="/deals" element={<DealList />} />
                    <Route path="/deals/pipeline" element={<DealPipeline />} />
                    <Route path="/deals/new" element={<DealForm />} />
                    <Route path="/deals/:id" element={<DealDetail />} />
                    <Route path="/deals/:id/edit" element={<DealForm />} />
                    <Route path="/notes" element={<NoteList />} />
                    <Route path="/calendar" element={<CalendarView />} />
                    <Route path="/emails" element={<EmailInbox />} />
                    <Route path="/panel" element={<PanelNavigation />} />
                    <Route path="/settings/users" element={<UserManagement />} />
                    <Route path="/settings/email-accounts" element={<EmailAccounts />} />
                    <Route path="/settings/email-templates" element={<EmailTemplates />} />
                </Route>
            </Routes>
        </BrowserRouter>
    );
}
