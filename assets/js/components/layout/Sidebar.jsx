import React, { useState } from 'react';
import { NavLink, useLocation, useNavigate } from 'react-router-dom';

const ICONS = {
    dashboard:  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4',
    clients:    'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    prospects:  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    filter:     'M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4-2A1 1 0 018 17v-3.586L3.293 6.707A1 1 0 013 6V4z',
    deals:      'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    calendar:   'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    notes:      'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    email:      'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    panel:      'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2',
    config:     'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    template:   'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
    chevronDown:  'M19 9l-7 7-7-7',
    chevronRight: 'M9 5l7 7-7 7',
};

function SvgIcon({ path, className = 'w-5 h-5 mr-3 shrink-0' }) {
    return (
        <svg className={className} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={path} />
        </svg>
    );
}

function NavItem({ to, label, icon, end = false }) {
    return (
        <NavLink to={to} end={end}
            className={({ isActive }) =>
                `flex items-center px-6 py-3 text-sm transition-colors ${
                    isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                }`
            }>
            <SvgIcon path={icon} />
            {label}
        </NavLink>
    );
}

function SubNavItem({ to, label, icon }) {
    return (
        <NavLink to={to}
            className={({ isActive }) =>
                `flex items-center pl-10 pr-6 py-2.5 text-sm transition-colors ${
                    isActive ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                }`
            }>
            <SvgIcon path={icon} className="w-4 h-4 mr-3 shrink-0" />
            {label}
        </NavLink>
    );
}

function FilterSubItem({ label, icon, statusParam }) {
    const location = useLocation();
    const navigate = useNavigate();

    const isActive = location.pathname === '/clients' && (() => {
        const sp = new URLSearchParams(location.search);
        if (statusParam === null) return !sp.get('status');
        return sp.get('status') === statusParam;
    })();

    const handleClick = (e) => {
        e.preventDefault();
        navigate(statusParam === null ? '/clients' : `/clients?status=${statusParam}`);
    };

    return (
        <a href="#" onClick={handleClick} aria-current={isActive ? 'page' : undefined}
            className={`flex items-center pl-10 pr-6 py-2.5 text-sm transition-colors ${
                isActive ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
            }`}>
            <SvgIcon path={icon} className="w-4 h-4 mr-3 shrink-0" />
            {label}
        </a>
    );
}

function CollapsibleSection({ label, icon, children, isActiveCheck }) {
    const location = useLocation();
    const hasActiveChild = isActiveCheck(location);
    const [open, setOpen] = useState(hasActiveChild);

    // Auto-open when navigating into this section
    React.useEffect(() => {
        if (hasActiveChild && !open) setOpen(true);
    }, [hasActiveChild]);

    return (
        <div>
            <button onClick={() => setOpen(!open)}
                className={`w-full flex items-center px-6 py-3 text-sm transition-colors ${
                    hasActiveChild && !open ? 'text-blue-400' : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                }`}>
                <SvgIcon path={icon} />
                <span className="flex-1 text-left">{label}</span>
                <SvgIcon
                    path={open ? ICONS.chevronDown : ICONS.chevronRight}
                    className="w-4 h-4 ml-auto mr-0 shrink-0 transition-transform"
                />
            </button>
            <div className={`overflow-hidden transition-all duration-200 ${open ? 'max-h-96' : 'max-h-0'}`}>
                {children}
            </div>
        </div>
    );
}

export default function Sidebar() {
    return (
        <aside className="w-64 bg-gray-900 text-white min-h-screen flex flex-col" role="navigation">
            <div className="p-6 border-b border-gray-700">
                <h1 className="text-xl font-bold tracking-wide">CRM Panel</h1>
            </div>
            <nav className="flex-1 py-4">
                <NavItem to="/" label="Dashboard" icon={ICONS.dashboard} end />

                <CollapsibleSection label="Klienci" icon={ICONS.clients}
                    isActiveCheck={(loc) => loc.pathname.startsWith('/clients')}>
                    <FilterSubItem label="Wszyscy"  icon={ICONS.clients}   statusParam={null} />
                    <FilterSubItem label="Prospekci" icon={ICONS.prospects} statusParam="prospect" />
                    <FilterSubItem label="Demo"      icon={ICONS.filter}    statusParam="demo" />
                    <FilterSubItem label="Aktywni"   icon={ICONS.filter}    statusParam="clients" />
                </CollapsibleSection>

                <NavItem to="/deals"    label="Umowy"     icon={ICONS.deals} />
                <NavItem to="/calendar" label="Kalendarz" icon={ICONS.calendar} />
                <NavItem to="/notes"    label="Notatki"   icon={ICONS.notes} />
                <NavItem to="/emails"   label="Email"     icon={ICONS.email} />
                <NavItem to="/panel"    label="Panele"    icon={ICONS.panel} />

                <CollapsibleSection label="Konfiguracja" icon={ICONS.config}
                    isActiveCheck={(loc) => loc.pathname.startsWith('/settings')}>
                    <SubNavItem to="/settings/users"           label="Uzytkownicy"     icon={ICONS.prospects} />
                    <SubNavItem to="/settings/email-accounts"  label="Konta e-mail"    icon={ICONS.email} />
                    <SubNavItem to="/settings/email-templates" label="Szablony e-mail" icon={ICONS.template} />
                </CollapsibleSection>
            </nav>
        </aside>
    );
}
