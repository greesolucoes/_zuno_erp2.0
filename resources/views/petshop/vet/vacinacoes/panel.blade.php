@extends('default.layout', ['title' => 'Painel de vacinação veterinária'])

@php
    use Illuminate\Support\Str;

    $formatMinutes = static function (?int $minutes): ?string {
        if ($minutes === null) {
            return null;
        }

        $minutes = abs($minutes);

        if ($minutes === 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($remaining > 0) {
            $parts[] = $remaining . 'min';
        }

        return implode(' ', $parts);
    };

    $dateLabel = Str::ucfirst($selectedDate->isoFormat('dddd, DD [de] MMMM'));
@endphp

@section('css')
    <style>
      

        .vet-queue__title {
            font-size: clamp(1.75rem, 3vw, 2.35rem);
            font-weight: 700;
            color: #201d4f;
        }

        .vet-queue__subtitle {
            color: #5d5c83;
            font-size: .95rem;
        }

        .vet-queue__metric {
            background: #fff;
            border-radius: 18px;
            padding: 1.15rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            border: 1px solid rgba(32, 29, 79, 0.08);
            box-shadow: 0 10px 28px rgba(32, 29, 79, 0.08);
            min-height: 130px;
        }

        .vet-queue__metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            color: #fff;
        }

        .vet-queue__metric--warning .vet-queue__metric-icon { background: linear-gradient(135deg, #ffda6a, #f59f00); }
        .vet-queue__metric--info .vet-queue__metric-icon { background: linear-gradient(135deg, #7b7be5, #4c3fb3); }
        .vet-queue__metric--primary .vet-queue__metric-icon { background: linear-gradient(135deg, #4c3fb3, #2f2678); }
        .vet-queue__metric--success .vet-queue__metric-icon { background: linear-gradient(135deg, #4ade80, #2f9e44); }

        .vet-queue__metric-value {
            font-size: clamp(1.8rem, 2.8vw, 2.5rem);
            font-weight: 700;
            color: #221f4f;
            margin-bottom: .25rem;
        }

        .vet-queue__metric-label {
            font-size: .95rem;
            font-weight: 600;
            color: #5d5c83;
        }

        .vet-queue__metric-description {
            margin: .35rem 0 0;
            font-size: .8rem;
            color: #7e7da8;
        }

        .vet-queue__date-picker input[type="date"] {
            background: #fff;
            border-radius: 12px;
            border: 1px solid rgba(76, 63, 179, 0.25);
            padding: .55rem .75rem;
            color: #201d4f;
        }

        .vet-queue__date-picker label {
            font-weight: 600;
            color: #4c3fb3;
            font-size: .85rem;
        }

        .vet-queue__board {
            background: transparent;
        }

        .vet-queue__column {
            background: #fff;
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid rgba(76, 63, 179, 0.12);
            min-height: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .vet-queue__column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .vet-queue__column-title {
            font-weight: 700;
            color: #2d2b6b;
            font-size: 1.05rem;
        }

        .vet-queue__column-subtitle {
            font-size: .8rem;
            color: #8887b2;
        }

        .vet-queue__pill {
            border-radius: 999px;
            padding: .2rem .75rem;
            font-weight: 600;
            font-size: .75rem;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }

        .vet-queue__pill--warning { background: rgba(255, 193, 7, 0.18); color: #b8870b; }
        .vet-queue__pill--info { background: rgba(91, 115, 232, 0.18); color: #524cc2; }
        .vet-queue__pill--primary { background: rgba(76, 63, 179, 0.12); color: #4c3fb3; }
        .vet-queue__pill--success { background: rgba(25, 135, 84, 0.18); color: #157347; }

        .vet-queue__column-body {
            display: flex;
            flex-direction: column;
            gap: .85rem;
        }

        .vet-queue__card {
            border: 1px solid rgba(76, 63, 179, 0.12);
            border-radius: 18px;
            padding: 1rem;
            background: linear-gradient(145deg, #fdfdff, #f5f5ff);
            display: flex;
            flex-direction: column;
            gap: .85rem;
            box-shadow: 0 12px 26px rgba(33, 33, 105, 0.08);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .vet-queue__card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(33, 33, 105, 0.12);
        }

        .vet-queue__card--delayed {
            border-color: rgba(220, 53, 69, 0.5);
            box-shadow: 0 16px 34px rgba(220, 53, 69, 0.18);
        }

        .vet-queue__card-header {
            display: flex;
            gap: .75rem;
        }

        .vet-queue__card-avatar {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            overflow: hidden;
            background: rgba(76, 63, 179, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .vet-queue__card-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vet-queue__card-title {
            font-weight: 700;
            color: #252364;
            margin-bottom: .25rem;
        }

        .vet-queue__card-subtitle {
            font-size: .8rem;
            color: #7a79a6;
        }

        .vet-queue__status {
            border-radius: 999px;
            padding: .2rem .65rem;
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .vet-queue__status--warning { background: rgba(255, 193, 7, 0.18); color: #b8860b; }
        .vet-queue__status--info { background: rgba(91, 115, 232, 0.18); color: #4c3fb3; }
        .vet-queue__status--success { background: rgba(25, 135, 84, 0.18); color: #157347; }
        .vet-queue__status--primary { background: rgba(76, 63, 179, 0.15); color: #3a2f9f; }
        .vet-queue__status--danger { background: rgba(220, 53, 69, 0.18); color: #b02a37; }

        .vet-queue__chips {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .vet-queue__chip {
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 600;
            padding: .25rem .65rem;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }

        .vet-queue__chip--warning { background: rgba(255, 193, 7, 0.18); color: #b8860b; }
        .vet-queue__chip--info { background: rgba(91, 115, 232, 0.18); color: #4c3fb3; }
        .vet-queue__chip--success { background: rgba(25, 135, 84, 0.18); color: #157347; }
        .vet-queue__chip--danger { background: rgba(220, 53, 69, 0.18); color: #b02a37; }
        .vet-queue__chip--neutral { background: rgba(32, 29, 79, 0.08); color: #2d2b6b; }

        .vet-queue__card-notes {
            background: rgba(76, 63, 179, 0.08);
            border-radius: 12px;
            padding: .65rem .75rem;
            font-size: .78rem;
            color: #3a358d;
        }

        .vet-queue__card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .vet-queue__doctor {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .vet-queue__doctor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(76, 63, 179, 0.12);
        }

        .vet-queue__doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vet-queue__doctor-name {
            font-weight: 600;
            color: #2f2c6d;
            font-size: .85rem;
        }

        .vet-queue__doctor-room {
            font-size: .7rem;
            color: #8b8abb;
        }

        .vet-queue__tutor {
            font-size: .75rem;
            color: #5c5a8f;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-weight: 600;
        }

        .vet-queue__empty {
            border: 1px dashed rgba(76, 63, 179, 0.25);
            border-radius: 14px;
            padding: 1.25rem;
            text-align: center;
            color: #8f8eb8;
            font-size: .85rem;
        }

        .vet-queue__sidebar-card {
            background: #fff;
            border-radius: 20px;
            padding: 1.35rem;
            border: 1px solid rgba(76, 63, 179, 0.12);
            box-shadow: 0 12px 28px rgba(32, 29, 79, 0.08);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .vet-queue__sidebar-title {
            font-weight: 700;
            color: #2d2b6b;
            font-size: 1rem;
        }

        .vet-queue__highlight {
            border-radius: 16px;
            padding: .9rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            border: 1px solid transparent;
        }

        .vet-queue__highlight--info { background: rgba(91, 115, 232, 0.14); border-color: rgba(91, 115, 232, 0.35); color: #3c3aa5; }
        .vet-queue__highlight--warning { background: rgba(255, 193, 7, 0.18); border-color: rgba(255, 193, 7, 0.35); color: #9f770b; }
        .vet-queue__highlight--danger { background: rgba(220, 53, 69, 0.18); border-color: rgba(220, 53, 69, 0.35); color: #a12531; }
        .vet-queue__highlight--success { background: rgba(25, 135, 84, 0.18); border-color: rgba(25, 135, 84, 0.3); color: #13653f; }

        .vet-queue__highlight strong {
            font-size: .95rem;
        }

        .vet-queue__highlight small {
            font-size: .72rem;
            color: inherit;
            opacity: .85;
        }

        .vet-queue__doctor-card {
            border: 1px solid rgba(76, 63, 179, 0.12);
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: .75rem;
            background: linear-gradient(135deg, #fdfdff, #f6f7ff);
        }

        .vet-queue__doctor-summary {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .vet-queue__doctor-summary .vet-queue__pill {
            margin-left: auto;
        }

        .vet-queue__doctor-stats {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem .75rem;
            font-size: .72rem;
            color: #6a6898;
        }

        .vet-queue__doctor-active,
        .vet-queue__doctor-next {
            font-size: .75rem;
            color: #494684;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .vet-queue__doctor-active i,
        .vet-queue__doctor-next i {
            font-size: .95rem;
        }

        .vet-queue__section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #2f2c6d;
        }

        .vet-queue__section-text {
            font-size: .85rem;
            color: #7a79a6;
        }

        .vet-queue__view-switch {
            background: #fff;
            border-radius: 999px;
            padding: .35rem;
            display: inline-flex;
            gap: .25rem;
            border: 1px solid rgba(76, 63, 179, 0.15);
            box-shadow: 0 14px 30px rgba(32, 29, 79, 0.1);
        }

        .vet-queue__view-switch .nav-link {
            border-radius: 999px;
            font-weight: 600;
            font-size: .9rem;
            padding: .55rem 1.25rem;
            color: #575585;
            background: transparent;
            border: none;
            transition: all .2s ease;
        }

        .vet-queue__view-switch .nav-link.active {
            background-color: #3a1e4b;
            color: #fff !important;
            box-shadow: 0 12px 30px rgba(119, 85, 230, 0.25);
        }
        .vet-queue__view-switch .nav-link:hover,
        .vet-queue__view-switch .nav-link:focus {
            background-color: rgba(114, 59, 233, 0.18);
            color: #3a1e4b !important;
        }

        .vet-queue__view-switch .nav-link i {
            font-size: 1.05rem;
        }

        .vet-calendar {
            background: linear-gradient(145deg, rgba(248, 249, 255, 0.95), rgba(230, 233, 255, 0.92));
            border-radius: 28px;
            padding: 2rem 1.75rem;
            border: 1px solid rgba(76, 63, 179, 0.12);
            box-shadow: 0 26px 48px rgba(30, 26, 85, 0.12);
            position: relative;
            overflow: hidden;
        }

        .vet-calendar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 15% 20%, rgba(91, 115, 232, 0.18), transparent 45%),
                radial-gradient(circle at 80% 0%, rgba(255, 182, 77, 0.18), transparent 55%);
            pointer-events: none;
        }

        .vet-calendar__hero {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: space-between;
            align-items: center;
        }

        .vet-calendar__hero-date {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .vet-calendar__hero-label {
            font-size: .85rem;
            font-weight: 600;
            color: #4c3fb3;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .vet-calendar__hero-date strong {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            color: #221f4f;
            font-weight: 700;
        }

        .vet-calendar__hero-stats {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .vet-calendar__hero-stat {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 18px;
            padding: .85rem 1.1rem;
            min-width: 160px;
            box-shadow: 0 12px 28px rgba(76, 63, 179, 0.08);
        }

        .vet-calendar__hero-stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2d2b6b;
            display: block;
            line-height: 1.2;
        }

        .vet-calendar__hero-stat-label {
            font-size: .75rem;
            color: #7a79a6;
            font-weight: 600;
            letter-spacing: .02em;
        }

        .vet-calendar__legend {
            position: relative;
            z-index: 1;
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .vet-calendar__legend-chip {
            border-radius: 999px;
            padding: .4rem .9rem;
            font-size: .78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255, 255, 255, 0.7);
            color: #403b7a;
            box-shadow: 0 8px 20px rgba(60, 56, 126, 0.12);
        }

        .vet-calendar__legend-chip--info {
            border: 1px solid rgba(91, 115, 232, 0.35);
            color: #4c3fb3;
        }

        .vet-calendar__legend-chip--warning {
            border: 1px solid rgba(255, 193, 7, 0.35);
            color: #b8860b;
        }

        .vet-calendar__legend-chip--primary {
            border: 1px solid rgba(76, 63, 179, 0.35);
            color: #2f2678;
        }

        .vet-calendar__legend-count {
            background: rgba(32, 29, 79, 0.08);
            border-radius: 999px;
            padding: .1rem .55rem;
            font-size: .7rem;
        }

        .vet-calendar__timeline {
            position: relative;
            z-index: 1;
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .vet-calendar__hour {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 1.25rem;
            position: relative;
        }

        .vet-calendar__hour-label {
            font-weight: 700;
            color: #5d5c83;
            font-size: .9rem;
            position: relative;
            padding-top: .35rem;
        }

        .vet-calendar__hour-label::after {
            content: '';
            position: absolute;
            right: -.65rem;
            top: .6rem;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7b7be5, #4c3fb3);
            box-shadow: 0 0 0 4px rgba(76, 63, 179, 0.18);
        }

        .vet-calendar__hour-track {
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }

        .vet-calendar__hour::before {
            content: '';
            position: absolute;
            left: 105px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, rgba(76, 63, 179, 0.25), rgba(91, 115, 232, 0.1));
        }

        .vet-calendar__event-card {
            position: relative;
            background: rgba(255, 255, 255, 0.88);
            border-radius: 20px;
            padding: 1rem 1.15rem;
            box-shadow: 0 18px 38px rgba(33, 33, 105, 0.12);
            border: 1px solid rgba(76, 63, 179, 0.12);
            backdrop-filter: blur(6px);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .vet-calendar__event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(33, 33, 105, 0.18);
        }

        .vet-calendar__event-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            z-index: 0;
            opacity: .12;
        }

        .vet-calendar__event-card--primary::before {
            background: linear-gradient(135deg, #4c3fb3, #2f2678);
        }

        .vet-calendar__event-card--success::before {
            background: linear-gradient(135deg, #4ade80, #2f9e44);
        }

        .vet-calendar__event-card--warning::before {
            background: linear-gradient(135deg, #ffda6a, #f59f00);
        }

        .vet-calendar__event-card--danger::before {
            background: linear-gradient(135deg, #ff6b6b, #d6336c);
        }

        .vet-calendar__event-card--info::before {
            background: linear-gradient(135deg, #7b7be5, #4c3fb3);
        }

        .vet-calendar__event-card.is-delayed {
            border-color: rgba(220, 53, 69, 0.4);
            box-shadow: 0 20px 40px rgba(220, 53, 69, 0.18);
        }

        .vet-calendar__event-header,
        .vet-calendar__event-footer {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .vet-calendar__event-time {
            font-weight: 700;
            color: #2d2b6b;
            font-size: .95rem;
        }

        .vet-calendar__event-duration {
            font-size: .75rem;
            font-weight: 600;
            color: #7a79a6;
            background: rgba(76, 63, 179, 0.08);
            border-radius: 999px;
            padding: .2rem .75rem;
        }

        .vet-calendar__event-body {
            position: relative;
            z-index: 1;
            margin: .75rem 0;
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .vet-calendar__event-patient {
            font-size: 1.05rem;
            font-weight: 700;
            color: #252364;
        }

        .vet-calendar__event-service {
            font-size: .85rem;
            color: #5d5c83;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .vet-calendar__event-meta {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            font-size: .78rem;
            color: #6f6da0;
            font-weight: 600;
        }

        .vet-calendar__event-tags {
            display: flex;
            gap: .4rem;
            flex-wrap: wrap;
        }

        .vet-calendar__event-tutor {
            font-size: .78rem;
            color: #6a6898;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .vet-calendar__hour-free {
            border: 1px dashed rgba(76, 63, 179, 0.18);
            border-radius: 16px;
            padding: .65rem .9rem;
            font-size: .75rem;
            color: #7a79a6;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(4px);
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .vet-calendar__unscheduled {
            position: relative;
            z-index: 1;
            margin-top: 2rem;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 22px;
            padding: 1.5rem;
            border: 1px dashed rgba(76, 63, 179, 0.2);
        }

        .vet-calendar__unscheduled-title {
            font-weight: 700;
            color: #2f2c6d;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .vet-calendar__unscheduled-title span {
            background: rgba(76, 63, 179, 0.1);
            border-radius: 999px;
            padding: .2rem .7rem;
            font-size: .75rem;
            color: #4c3fb3;
            font-weight: 600;
        }

        .vet-calendar__unscheduled-list {
            display: grid;
            gap: .9rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .vet-calendar__unscheduled-card {
            background: rgba(248, 249, 255, 0.8);
            border-radius: 16px;
            padding: .9rem;
            border: 1px solid rgba(76, 63, 179, 0.12);
            display: flex;
            flex-direction: column;
            gap: .45rem;
            box-shadow: 0 12px 24px rgba(32, 29, 79, 0.08);
        }

        .vet-calendar__unscheduled-patient {
            font-weight: 700;
            color: #2d2b6b;
        }

        .vet-calendar__unscheduled-meta {
            display: flex;
            flex-direction: column;
            gap: .2rem;
            font-size: .75rem;
            color: #6f6da0;
            font-weight: 600;
        }

        .vet-calendar__empty {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 3rem 1rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px dashed rgba(76, 63, 179, 0.22);
            color: #6a6898;
            font-weight: 600;
            margin-top: 2rem;
        }

        .vet-calendar__empty i {
            font-size: 2.8rem;
            color: #4c3fb3;
        }

        @media (max-width: 991px) {
            .vet-queue__metric {
                min-height: auto;
            }

            .vet-calendar {
                padding: 1.6rem 1.25rem;
            }

            .vet-calendar__hour {
                grid-template-columns: 90px 1fr;
            }

            .vet-calendar__hero-stats {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid vet-queue py-3">
        <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-lg">
                <h1 class="vet-queue__title mb-1">Painel de vacinação</h1>
                <p class="vet-queue__subtitle mb-0">
                    Acompanhe em tempo real as vacinações programadas para {{ $dateLabel }}.
                </p>
            </div>
            <div class="col-12 col-lg-auto">
                <form method="get" class="vet-queue__date-picker d-flex align-items-end gap-2 flex-wrap">
                    <div class="d-flex flex-column">
                        <label for="queue-date">Escolher dia</label>
                        <input
                            id="queue-date"
                            type="date"
                            name="date"
                            value="{{ $selectedDate->format('Y-m-d') }}"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-refresh-line me-1"></i>
                        Atualizar visão
                    </button>
                </form>
            </div>
        </div>

        <div class="row g-3">
            @foreach ($metrics as $metric)
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="vet-queue__metric vet-queue__metric--{{ $metric['variant'] }}">
                        <div class="vet-queue__metric-icon">
                            <i class="{{ $metric['icon'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="vet-queue__metric-value">{{ $metric['value'] }}</div>
                            <div class="vet-queue__metric-label">{{ $metric['label'] }}</div>
                            <p class="vet-queue__metric-description mb-0">{{ $metric['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mt-4">
            <div>
                <div class="vet-queue__section-title mb-1">Como deseja visualizar?</div>
                <p class="vet-queue__section-text mb-0">Alterne entre o quadro dinâmico e a agenda artística do dia.</p>
            </div>
            <div class="vet-queue__view-switch nav nav-pills" id="vet-queue-tab" role="tablist">
                <button class="nav-link active" id="vet-queue-tab-board" data-bs-toggle="pill" data-bs-target="#vet-queue-pane-board" type="button" role="tab" aria-controls="vet-queue-pane-board" aria-selected="true">
                    <i class="ri-layout-grid-line me-1"></i>
                    Quadro Kanban
                </button>
                <button class="nav-link" id="vet-queue-tab-calendar" data-bs-toggle="pill" data-bs-target="#vet-queue-pane-calendar" type="button" role="tab" aria-controls="vet-queue-pane-calendar" aria-selected="false">
                    <i class="ri-calendar-line me-1"></i>
                    Agenda diária
                </button>
            </div>
        </div>

        <div class="tab-content mt-3" id="vet-queue-tab-content">
            <div class="tab-pane fade show active" id="vet-queue-pane-board" role="tabpanel" aria-labelledby="vet-queue-tab-board">
                <div class="row g-4 mt-1">
            <div class="col-12 col-xxl-8">
                @php
                    $columns = [
                        'in_progress' => [
                            'title' => 'Em aplicação',
                            'subtitle' => 'Vacinações em andamento',
                            'variant' => 'info',
                            'empty' => 'Nenhuma vacinação está em execução agora.',
                        ],
                        'waiting' => [
                            'title' => 'Preparação',
                            'subtitle' => 'Pacientes prontos para aplicar',
                            'variant' => 'warning',
                            'empty' => 'Nenhum paciente aguardando aplicação.',
                        ],
                        'upcoming' => [
                            'title' => 'Próximas doses',
                            'subtitle' => 'Agendadas para hoje',
                            'variant' => 'primary',
                            'empty' => 'Todos os horários previstos já foram iniciados.',
                        ],
                    ];
                @endphp

                <div class="vet-queue__board">
                    <div class="row g-3">
                        @foreach ($columns as $key => $column)
                            @php
                                $columnItems = collect($groupedQueue[$key] ?? []);
                            @endphp
                            <div class="col-12 col-lg-4">
                                <div class="vet-queue__column">
                                    <div class="vet-queue__column-header">
                                        <div>
                                            <div class="vet-queue__column-title">{{ $column['title'] }}</div>
                                            <div class="vet-queue__column-subtitle">{{ $column['subtitle'] }}</div>
                                        </div>
                                        <span class="vet-queue__pill vet-queue__pill--{{ $column['variant'] }}">
                                            <i class="ri-team-line"></i>
                                            {{ $columnItems->count() }}
                                        </span>
                                    </div>

                                    <div class="vet-queue__column-body">
                                        @forelse ($columnItems as $item)
                                            @php
                                                $waitingMinutes = $item['waiting_minutes'] ?? 0;
                                                $minutesToStart = $item['minutes_to_start'] ?? null;
                                                $elapsedMinutes = $item['elapsed_minutes'] ?? null;
                                                $isEmergency = !empty($item['priority']) && Str::contains(Str::lower($item['priority']), ['emerg', 'crit']);
                                                $statusColor = $item['status']['color'] ?? 'primary';
                                                $notes = $item['notes'] ?? null;
                                            @endphp

                                            <article class="vet-queue__card{{ !empty($item['is_delayed']) ? ' vet-queue__card--delayed' : '' }}">
                                                <div class="vet-queue__card-header">
                                                    <div class="vet-queue__card-avatar">
                                                        <img src="{{ $item['patient']['avatar'] }}" alt="{{ $item['patient']['name'] }}">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                                            <div>
                                                                <div class="vet-queue__card-title">{{ $item['patient']['name'] }}</div>
                                                                <div class="vet-queue__card-subtitle">
                                                                    {{ $item['patient']['species'] }}
                                                                    @if (!empty($item['service']))
                                                                        • {{ $item['service'] }}
                                                                    @endif
                                                                    @if (!empty($item['dose_count']) && $item['dose_count'] > 1)
                                                                        • {{ $item['dose_count'] }} {{ Str::plural('dose', $item['dose_count']) }}
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="vet-queue__status vet-queue__status--{{ $statusColor }}">
                                                                {{ $item['status']['label'] ?? '—' }}
                                                            </span>
                                                        </div>

                                                        <div class="vet-queue__chips mt-2">
                                                            @if ($item['scheduled_for'])
                                                                <span class="vet-queue__chip vet-queue__chip--neutral">
                                                                    <i class="ri-time-line"></i>
                                                                    {{ $item['scheduled_for'] }}
                                                                </span>
                                                            @endif

                                                            @if ($waitingMinutes > 0)
                                                                <span class="vet-queue__chip vet-queue__chip--warning">
                                                                    <i class="ri-hourglass-line"></i>
                                                                    Aguardando {{ $formatMinutes($waitingMinutes) }}
                                                                </span>
                                                            @endif

                                                            @if ($minutesToStart !== null && $minutesToStart > 0)
                                                                <span class="vet-queue__chip vet-queue__chip--info">
                                                                    <i class="ri-calendar-check-line"></i>
                                                                    Começa em {{ $formatMinutes($minutesToStart) }}
                                                                </span>
                                                            @endif

                                                            @if ($elapsedMinutes)
                                                                <span class="vet-queue__chip vet-queue__chip--success">
                                                                    <i class="ri-syringe-line"></i>
                                                                    Em aplicação há {{ $formatMinutes($elapsedMinutes) }}
                                                                </span>
                                                            @endif

                                                            @if ($isEmergency)
                                                                <span class="vet-queue__chip vet-queue__chip--danger">
                                                                    <i class="ri-flashlight-line"></i>
                                                                    Prioridade: {{ $item['priority'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($notes)
                                                    <div class="vet-queue__card-notes">
                                                        <i class="ri-information-line me-1"></i>
                                                        {{ Str::limit($notes, 140) }}
                                                    </div>
                                                @endif

                                                <div class="vet-queue__card-footer">
                                                    <div class="vet-queue__doctor">
                                                        <div class="vet-queue__doctor-avatar">
                                                            <img src="{{ $item['veterinarian']['avatar'] }}" alt="{{ $item['veterinarian']['name'] }}">
                                                        </div>
                                                        <div>
                                                            <div class="vet-queue__doctor-name">{{ $item['veterinarian']['name'] }}</div>
                                                            <div class="vet-queue__doctor-room">
                                                                {{ $item['room'] ? 'Sala ' . $item['room'] : 'Sala não definida' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column align-items-end gap-2">
                                                        @if (!empty($item['tutor']))
                                                            <div class="vet-queue__tutor">
                                                                <i class="ri-user-heart-line"></i>
                                                                {{ $item['tutor'] }}
                                                            </div>
                                                        @endif
                                                        <a href="{{ route('vet.vaccinations.apply', ['vacinacao' => $item['id']]) }}"
                                                            class="btn btn-success btn-sm">
                                                            <i class="ri-syringe-line me-1"></i>
                                                            Aplicar vacinação
                                                        </a>
                                                    </div>
                                                </div>
                                            </article>
                                        @empty
                                            <div class="vet-queue__empty">
                                                <i class="ri-emotion-line fs-4 d-block mb-2"></i>
                                                {{ $column['empty'] }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12 col-xxl-4 d-flex flex-column gap-3">
                <div class="vet-queue__sidebar-card">
                    <div class="vet-queue__sidebar-title">Destaques em tempo real</div>
                    @php
                        $current = $highlights['current'] ?? null;
                        $next = $highlights['next'] ?? null;
                        $delayed = $highlights['delayed'] ?? null;
                        $priority = $highlights['priority'] ?? null;
                    @endphp

                    @if ($current)
                        <div class="vet-queue__highlight vet-queue__highlight--info">
                            <strong><i class="ri-syringe-line me-1"></i> Aplicação em andamento</strong>
                            <div>{{ $current['patient']['name'] }} com {{ $current['veterinarian']['name'] }}</div>
                            <small>Iniciada há {{ $formatMinutes($current['elapsed_minutes'] ?? 0) }}</small>
                        </div>
                    @endif

                    @if ($next)
                        <div class="vet-queue__highlight vet-queue__highlight--success">
                            <strong><i class="ri-roadster-fill me-1"></i> Próximo a vacinar</strong>
                            <div>{{ $next['patient']['name'] }} • {{ $next['service'] }}</div>
                            @if (!empty($next['scheduled_for']))
                                <small>Previsto para {{ $next['scheduled_for'] }}</small>
                            @elseif (!empty($next['waiting_minutes']))
                                <small>Aguardando há {{ $formatMinutes($next['waiting_minutes']) }}</small>
                            @endif
                        </div>
                    @endif

                    @if ($delayed)
                        <div class="vet-queue__highlight vet-queue__highlight--warning">
                            <strong><i class="ri-time-line me-1"></i> Atenção ao atraso</strong>
                            <div>{{ $delayed['patient']['name'] }} aguarda há {{ $formatMinutes($delayed['waiting_minutes'] ?? 0) }}</div>
                            <small>Responsável: {{ $delayed['veterinarian']['name'] }}</small>
                        </div>
                    @endif

                    @if ($priority)
                        <div class="vet-queue__highlight vet-queue__highlight--danger">
                            <strong><i class="ri-alert-line me-1"></i> Prioridade crítica</strong>
                            <div>{{ $priority['patient']['name'] }} • {{ $priority['priority'] }}</div>
                            <small>Contato do tutor: {{ $priority['tutor'] }}</small>
                        </div>
                    @endif

                    @if (! $current && ! $next && ! $delayed && ! $priority)
                        <div class="vet-queue__highlight vet-queue__highlight--info">
                            <strong>Fila de vacinação estável</strong>
                            <small>Sem alertas críticos neste momento.</small>
                        </div>
                    @endif
                </div>

                @if (!empty($veterinarianBoards))
                    <div class="vet-queue__sidebar-card">
                        <div class="vet-queue__sidebar-title">Capacidade por veterinário</div>
                        <div class="d-flex flex-column gap-3">
                            @foreach ($veterinarianBoards as $board)
                                <div class="vet-queue__doctor-card">
                                    <div class="vet-queue__doctor-summary">
                                        <div class="vet-queue__doctor-avatar">
                                            <img src="{{ $board['avatar'] }}" alt="{{ $board['name'] }}">
                                        </div>
                                        <div>
                                            <div class="vet-queue__doctor-name">{{ $board['name'] }}</div>
                                            <div class="vet-queue__doctor-stats">
                                                <span><i class="ri-time-line"></i> {{ $board['waiting'] }} em espera</span>
                                                <span><i class="ri-syringe-line"></i> {{ $board['in_progress'] }} em aplicação</span>
                                                <span><i class="ri-calendar-check-line"></i> {{ $board['upcoming'] }} próximos</span>
                                            </div>
                                        </div>
                                        <span class="vet-queue__pill vet-queue__pill--primary">{{ $board['total_today'] }} hoje</span>
                                    </div>

                                    @if (!empty($board['active']))
                                        <div class="vet-queue__doctor-active">
                                            <i class="ri-pulse-line"></i>
                                            {{ $board['active']['patient']['name'] }} em aplicação
                                        </div>
                                    @endif

                                    @if (!empty($board['next']))
                                        <div class="vet-queue__doctor-next">
                                            <i class="ri-arrow-right-line"></i>
                                            Próximo: {{ $board['next']['patient']['name'] }}
                                            @if (!empty($board['next']['scheduled_for']))
                                                às {{ $board['next']['scheduled_for'] }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
                </div>
            </div>
            <div class="tab-pane fade" id="vet-queue-pane-calendar" role="tabpanel" aria-labelledby="vet-queue-tab-calendar">
                <div class="vet-calendar mt-2">
                    <div class="vet-calendar__hero">
                        <div class="vet-calendar__hero-date">
                            <span class="vet-calendar__hero-label">Agenda de vacinações</span>
                            <strong>{{ $dateLabel }}</strong>
                        </div>
                        <div class="vet-calendar__hero-stats">
                            <div class="vet-calendar__hero-stat">
                                <span class="vet-calendar__hero-stat-value">{{ $calendarView['summary']['scheduled'] }}</span>
                                <span class="vet-calendar__hero-stat-label">Vacinações com horário</span>
                            </div>
                            <div class="vet-calendar__hero-stat">
                                <span class="vet-calendar__hero-stat-value">{{ $calendarView['summary']['in_progress'] }}</span>
                                <span class="vet-calendar__hero-stat-label">Em aplicação agora</span>
                            </div>
                            <div class="vet-calendar__hero-stat">
                                <span class="vet-calendar__hero-stat-value">@if ($calendarView['summary']['peak_hour']){{ $calendarView['summary']['peak_hour'] }}@else—@endif</span>
                                <span class="vet-calendar__hero-stat-label">@if ($calendarView['summary']['peak_hour'])Pico com {{ $calendarView['summary']['peak_count'] }} vacinações @else Agenda equilibrada @endif</span>
                            </div>
                        </div>
                    </div>

                    @if (!empty($calendarView['legend']))
                        <div class="vet-calendar__legend">
                            @foreach ($calendarView['legend'] as $legend)
                                <span class="vet-calendar__legend-chip vet-calendar__legend-chip--{{ $legend['variant'] }}">
                                    <i class="{{ $legend['icon'] }} me-1"></i>{{ $legend['label'] }}
                                    <span class="vet-calendar__legend-count">{{ $legend['count'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if ($calendarView['summary']['scheduled'] > 0)
                        <div class="vet-calendar__timeline mt-4">
                            @foreach ($calendarView['hours'] as $hour)
                                <div class="vet-calendar__hour">
                                    <div class="vet-calendar__hour-label">{{ $hour['label'] }}</div>
                                    <div class="vet-calendar__hour-track">
                                        @forelse ($hour['events'] as $event)
                                            <article class="vet-calendar__event-card vet-calendar__event-card--{{ $event['status_color'] ?? 'primary' }}{{ !empty($event['is_delayed']) ? ' is-delayed' : '' }}">
                                                <header class="vet-calendar__event-header">
                                                    <span class="vet-calendar__event-time"><i class="ri-time-line me-1"></i>{{ $event['start_time'] }} - {{ $event['end_time'] }}</span>
                                                    <span class="vet-calendar__event-duration">{{ $event['duration_label'] }}</span>
                                                </header>
                                                <div class="vet-calendar__event-body">
                                                    <div class="vet-calendar__event-patient">{{ $event['patient'] }}</div>
                                                    @if (!empty($event['service']))
                                                        <div class="vet-calendar__event-service"><i class="ri-syringe-line me-1"></i>{{ $event['service'] }}</div>
                                                    @endif
                                                    @if (!empty($event['tutor']))
                                                        <div class="vet-calendar__event-tutor"><i class="ri-user-smile-line me-1"></i>{{ $event['tutor'] }}</div>
                                                    @endif
                                                </div>
                                                <footer class="vet-calendar__event-footer">
                                                    <div class="vet-calendar__event-meta">
                                                        @if (!empty($event['veterinarian']))
                                                            <span><i class="ri-user-heart-line me-1"></i>{{ $event['veterinarian'] }}</span>
                                                        @endif
                                                        @if (!empty($event['room']))
                                                            <span><i class="ri-home-smile-line me-1"></i>{{ $event['room'] }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="vet-calendar__event-tags">
                                                        <span class="badge bg-{{ $event['status_color'] ?? 'primary' }}-subtle text-{{ $event['status_color'] ?? 'primary' }}">{{ $event['category_label'] }}</span>
                                                        @if (!empty($event['priority']))
                                                            <span class="badge bg-danger-subtle text-danger"><i class="ri-flashlight-fill me-1"></i>{{ $event['priority'] }}</span>
                                                        @endif
                                                        @if (!empty($event['is_delayed']))
                                                            <span class="badge bg-danger-subtle text-danger"><i class="ri-alert-line me-1"></i>Atraso</span>
                                                        @elseif (!empty($event['waiting_minutes']) && $event['waiting_minutes'] > 0 && (($event['minutes_to_start'] ?? 0) <= 0))
                                                            <span class="badge bg-warning-subtle text-warning"><i class="ri-time-line me-1"></i>Aguardando {{ $formatMinutes($event['waiting_minutes']) }}</span>
                                                        @elseif (!empty($event['minutes_to_start']) && $event['minutes_to_start'] > 0)
                                                            <span class="badge bg-info-subtle text-info"><i class="ri-calendar-check-line me-1"></i>Começa em {{ $formatMinutes($event['minutes_to_start']) }}</span>
                                                        @endif
                                                    </div>
                                                </footer>
                                            </article>
                                        @empty
                                            <div class="vet-calendar__hour-free">
                                                <i class="ri-sparkling-2-line"></i>
                                                Horário livre
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="vet-calendar__empty">
                            <i class="ri-calendar-event-line d-block mb-2"></i>
                            Nenhum horário definido para esta data.
                            @if ($calendarView['summary']['unscheduled'] > 0)
                                <span class="d-block mt-2">Ainda há {{ $calendarView['summary']['unscheduled'] }} vacinações aguardando definição.</span>
                            @endif
                        </div>
                    @endif

                    @if (!empty($calendarView['unscheduled']))
                        <div class="vet-calendar__unscheduled">
                            <div class="vet-calendar__unscheduled-title">
                                <i class="ri-compass-3-line"></i>
                                Sem horário definido
                                <span>{{ $calendarView['summary']['unscheduled'] }}</span>
                            </div>
                            <div class="vet-calendar__unscheduled-list">
                                @foreach ($calendarView['unscheduled'] as $event)
                                    <div class="vet-calendar__unscheduled-card">
                                        <div class="vet-calendar__unscheduled-patient">{{ $event['patient'] }}</div>
                                        <div class="vet-calendar__unscheduled-meta">
                                            @if (!empty($event['service']))
                                                <span><i class="ri-syringe-line me-1"></i>{{ $event['service'] }}</span>
                                            @endif
                                            @if (!empty($event['veterinarian']))
                                                <span><i class="ri-user-heart-line me-1"></i>{{ $event['veterinarian'] }}</span>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge bg-{{ $event['status_color'] ?? 'primary' }}-subtle text-{{ $event['status_color'] ?? 'primary' }}">{{ $event['category_label'] }}</span>
                                            @if (!empty($event['priority']))
                                                <span class="badge bg-danger-subtle text-danger"><i class="ri-alert-line me-1"></i>{{ $event['priority'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection