@extends('layouts/lecturer')

@section('title', 'Reports')

@push('styles')
    <style>
        .lecturer-reports {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .lecturer-reports__title-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .lecturer-reports__title-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 0.85rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .lecturer-reports__title {
            margin: 0;
            font-size: 2.25rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        .lecturer-reports__card {
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .lecturer-reports__card-body {
            padding: 1.25rem;
        }

        .lecturer-reports__section-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #334155;
        }

        .lecturer-reports__section-subtitle {
            margin: 0.35rem 0 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .lecturer-reports__types {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 1.25rem;
        }

        .lecturer-reports__radio {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.92rem;
            color: #475569;
            font-weight: 500;
        }

        .lecturer-reports__radio input {
            accent-color: #6d28d9;
        }

        .lecturer-reports__form {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .lecturer-reports__field {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .lecturer-reports__field label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
        }

        .lecturer-reports__input-wrap {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-height: 3rem;
            padding: 0 0.95rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            background: #fff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.03);
        }

        .lecturer-reports__input-wrap svg {
            width: 1rem;
            height: 1rem;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .lecturer-reports__input,
        .lecturer-reports__select {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #334155;
            font-size: 0.92rem;
        }

        .lecturer-reports__actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }

        .lecturer-reports__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 11rem;
            min-height: 3rem;
            padding: 0.75rem 1.25rem;
            border: 0;
            border-radius: 0.85rem;
            background: linear-gradient(90deg, #5b21b6 0%, #7c3aed 100%);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 700;
            box-shadow: 0 14px 30px rgba(124, 58, 237, 0.24);
        }

        .lecturer-reports__history-head {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 1.25rem;
        }

        .lecturer-reports__history-head svg {
            width: 1.2rem;
            height: 1.2rem;
            color: #475569;
        }

        .lecturer-reports__table-wrap {
            overflow-x: auto;
        }

        .lecturer-reports__table {
            width: 100%;
            min-width: 680px;
            border-collapse: collapse;
        }

        .lecturer-reports__table th,
        .lecturer-reports__table td {
            padding: 1rem 0.9rem;
            text-align: left;
            border-bottom: 1px solid #eef2f7;
        }

        .lecturer-reports__table th {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            background: #fcfdff;
        }

        .lecturer-reports__table td {
            font-size: 0.92rem;
            color: #64748b;
        }

        .lecturer-reports__download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-height: 2.2rem;
            padding: 0.45rem 0.9rem;
            border: 1px solid #8b5cf6;
            border-radius: 0.7rem;
            background: #fff;
            color: #6d28d9;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
        }

        .lecturer-reports__download svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        @media (min-width: 768px) {
            .lecturer-reports__form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lecturer-reports__actions {
                grid-column: 1 / -1;
            }
        }
    </style>
@endpush

@section('content')
<div class="lecturer-reports">
    <div class="lecturer-reports__title-row">
        <div class="lecturer-reports__title-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 21a1.5 1.5 0 0 0 1.5-1.5V7.071a1.5 1.5 0 0 0-.44-1.06l-4.57-4.571A1.5 1.5 0 0 0 14.93 1H6a1.5 1.5 0 0 0-1.5 1.5v17A1.5 1.5 0 0 0 6 21h13.5ZM15 1.5V6a1.5 1.5 0 0 0 1.5 1.5H21" />
            </svg>
        </div>
        <h1 class="lecturer-reports__title">Reports Generation</h1>
    </div>

    <section class="lecturer-reports__card">
        <div class="lecturer-reports__card-body">
            <h2 class="lecturer-reports__section-title">Report Configuration</h2>

            <div class="lecturer-reports__types">
                <label class="lecturer-reports__radio">
                    <input type="radio" name="report_type">
                    <span>Attendance</span>
                </label>
                <label class="lecturer-reports__radio">
                    <input type="radio" name="report_type">
                    <span>Hafazan Progress</span>
                </label>
                <label class="lecturer-reports__radio">
                    <input type="radio" name="report_type" checked>
                    <span>Warning Letter</span>
                </label>
            </div>

            <form class="lecturer-reports__form">
                <div class="lecturer-reports__field">
                    <label for="report-start-date">Start Date</label>
                    <div class="lecturer-reports__input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75h15A2.25 2.25 0 0 1 21.75 9v9.75A2.25 2.25 0 0 1 19.5 21h-15a2.25 2.25 0 0 1-2.25-2.25V9A2.25 2.25 0 0 1 4.5 6.75Z" />
                        </svg>
                        <input id="report-start-date" type="text" class="lecturer-reports__input" placeholder="">
                    </div>
                </div>

                <div class="lecturer-reports__field">
                    <label for="report-end-date">End Date</label>
                    <div class="lecturer-reports__input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75h15A2.25 2.25 0 0 1 21.75 9v9.75A2.25 2.25 0 0 1 19.5 21h-15a2.25 2.25 0 0 1-2.25-2.25V9A2.25 2.25 0 0 1 4.5 6.75Z" />
                        </svg>
                        <input id="report-end-date" type="text" class="lecturer-reports__input" placeholder="">
                    </div>
                </div>

                <div class="lecturer-reports__field">
                    <label for="report-class">Class / Batch</label>
                    <div class="lecturer-reports__input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.666-6.358-1.816a6.75 6.75 0 0 1-.037-.666v-.031m12.395-3.199a5.25 5.25 0 0 0-10.395 0m10.395 0a5.25 5.25 0 0 1 0 0ZM15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <select id="report-class" class="lecturer-reports__select">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="lecturer-reports__field">
                    <label for="report-student">Student (Optional)</label>
                    <div class="lecturer-reports__input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.75 17.25a7.5 7.5 0 0 0 9.9-.6Z" />
                        </svg>
                        <input id="report-student" type="text" class="lecturer-reports__input" placeholder="Search student name...">
                    </div>
                </div>

                <div class="lecturer-reports__actions">
                    <button type="button" class="lecturer-reports__button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 21a1.5 1.5 0 0 0 1.5-1.5V7.071a1.5 1.5 0 0 0-.44-1.06l-4.57-4.571A1.5 1.5 0 0 0 14.93 1H6a1.5 1.5 0 0 0-1.5 1.5v17A1.5 1.5 0 0 0 6 21h13.5ZM15 1.5V6a1.5 1.5 0 0 0 1.5 1.5H21" />
                        </svg>
                        <span>Generate Report</span>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="lecturer-reports__card">
        <div class="lecturer-reports__card-body">
            <div class="lecturer-reports__history-head">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 0V9m0-2.25h2.25m-2.25 0h-2.25M12 6.75a8.25 8.25 0 1 0 8.25 8.25" />
                </svg>
                <h2 class="lecturer-reports__section-title">Report History</h2>
            </div>

            <div class="lecturer-reports__table-wrap">
                <table class="lecturer-reports__table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Report Type</th>
                            <th>Date Generated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Warning Letter</td>
                            <td>01-12-2025&nbsp;&nbsp;08:59 PM</td>
                            <td>
                                <a href="#" class="lecturer-reports__download">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 12 4.5-4.5M12 16.5 7.5 12m-3 6.75h15" />
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Attendance</td>
                            <td>03-12-2025&nbsp;&nbsp;08:59 PM</td>
                            <td>
                                <a href="#" class="lecturer-reports__download">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 12 4.5-4.5M12 16.5 7.5 12m-3 6.75h15" />
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Hafazan Progress</td>
                            <td>12-12-2025&nbsp;&nbsp;08:59 PM</td>
                            <td>
                                <a href="#" class="lecturer-reports__download">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 12 4.5-4.5M12 16.5 7.5 12m-3 6.75h15" />
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Warning Letter</td>
                            <td>20-12-2025&nbsp;&nbsp;08:59 PM</td>
                            <td>
                                <a href="#" class="lecturer-reports__download">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 12 4.5-4.5M12 16.5 7.5 12m-3 6.75h15" />
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
