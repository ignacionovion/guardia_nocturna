@extends('layouts.guardia-live')

@php
    $guardiaName = $initialState['guardia']['name'] ?? 'Guardia';
@endphp

@section('title', 'Panel en Vivo — ' . $guardiaName)
