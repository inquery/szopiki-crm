import React, { useState, useRef } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';

export default function CalendarView() {
    const calendarRef = useRef(null);
    const { success, error: showError } = useNotification();
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ title: '', start_at: '', end_at: '', location: '', type: 'in_person', description: '' });
    const [submitting, setSubmitting] = useState(false);

    const fetchEvents = async (info) => {
        try {
            const res = await apiClient.get('/calendar/events', {
                params: { start: info.startStr, end: info.endStr }
            });
            return res.data;
        } catch { return []; }
    };

    const handleDateClick = (arg) => {
        const startDate = arg.dateStr + 'T09:00';
        const endDate = arg.dateStr + 'T10:00';
        setForm({ ...form, start_at: startDate, end_at: endDate });
        setShowForm(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            await apiClient.post('/meetings', form);
            success('Spotkanie dodane');
            setShowForm(false);
            setForm({ title: '', start_at: '', end_at: '', location: '', type: 'in_person', description: '' });
            calendarRef.current?.getApi().refetchEvents();
        } catch (err) { showError(err.response?.data?.message || 'Blad'); }
        finally { setSubmitting(false); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Kalendarz</h2>
                <button onClick={() => setShowForm(true)} className="btn-primary">Nowe spotkanie</button>
            </div>
            <div className="card">
                <FullCalendar
                    ref={calendarRef}
                    plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin]}
                    initialView="dayGridMonth"
                    headerToolbar={{ left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' }}
                    locale="pl"
                    events={fetchEvents}
                    dateClick={handleDateClick}
                    eventClick={(info) => { window.location.href = `/meetings/${info.event.id}`; }}
                    height="auto"
                    firstDay={1}
                />
            </div>
            {showForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4">
                        <h3 className="text-lg font-semibold mb-4">Nowe spotkanie</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div><label className="label">Tytul *</label><input value={form.title} onChange={e => setForm({...form, title: e.target.value})} className="input" required /></div>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Poczatek *</label><input type="datetime-local" value={form.start_at} onChange={e => setForm({...form, start_at: e.target.value})} className="input" required /></div>
                                <div><label className="label">Koniec *</label><input type="datetime-local" value={form.end_at} onChange={e => setForm({...form, end_at: e.target.value})} className="input" required /></div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Lokalizacja</label><input value={form.location || ''} onChange={e => setForm({...form, location: e.target.value})} className="input" /></div>
                                <div>
                                    <label className="label">Typ</label>
                                    <select value={form.type} onChange={e => setForm({...form, type: e.target.value})} className="input">
                                        <option value="in_person">Osobiste</option><option value="phone">Telefoniczne</option><option value="video">Video</option>
                                    </select>
                                </div>
                            </div>
                            <div><label className="label">Opis</label><textarea value={form.description || ''} onChange={e => setForm({...form, description: e.target.value})} className="input" rows="2" /></div>
                            <div className="flex justify-end gap-3">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Anuluj</button>
                                <button type="submit" disabled={submitting} className="btn-primary">{submitting ? 'Zapisywanie...' : 'Zapisz'}</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
