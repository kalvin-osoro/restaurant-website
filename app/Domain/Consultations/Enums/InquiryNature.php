<?php

namespace App\Domain\Consultations\Enums;

enum InquiryNature: string
{
    case GeneralConsultation = 'General Consultation';
    case CorporateRepresentation = 'Corporate Representation';
    case CaseReferral = 'Case Referral';
    case MergersAndAcquisitions = 'Mergers & Acquisitions';
    case IntellectualProperty = 'Intellectual Property';
    case CommercialDispute = 'Commercial Dispute';
    case Other = 'Other';
    case OtherInstitutionalMatter = 'Other Institutional Matter';
}
