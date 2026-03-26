<?php

namespace App\Enums;

enum ActivityAction: string
{
    case FarmerRegistered = 'farmer_registered';
    case DiagnosisSubmitted = 'diagnosis_submitted';
    case DiagnosisCompleted = 'diagnosis_completed';
    case DiagnosisFailed = 'diagnosis_failed';
    case AdminUserStatusToggled = 'admin_user_status_toggled';
}
