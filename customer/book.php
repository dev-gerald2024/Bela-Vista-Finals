<?php
/**
 * Bella Vista Oasis Spa - Multi-Step Online Treatment Booking Wizard
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    flash_message('warning', 'Please sign in to make a treatment reservation.');
    redirect('../login.php');
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerId = (int)($_SESSION['customer_id'] ?? 0);

// Fetch Active Services
$stServices = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY base_price ASC");
$stServices->execute();
$services = $stServices->fetchAll();

// Fetch Active Therapists
$stTherapists = $pdo->prepare("SELECT t.id, t.specialization, t.rating_avg, u.first_name, u.last_name
                               FROM therapists t
                               JOIN employees e ON t.employee_id = e.id
                               JOIN users u ON e.user_id = u.id
                               WHERE t.is_available = 1 AND e.employment_status = 'active'
                               ORDER BY u.first_name ASC");
$stTherapists->execute();
$therapists = $stTherapists->fetchAll();

// Preselect service if query param exists
$preselectedServiceId = (int)($_GET['service_id'] ?? 0);

$pageTitle = 'Reserve Sanctuary Experience';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Booking Wizard Container -->
<div class="row justify-content-center">
    <div class="col-xl-11 col-xxl-10">
        <!-- Wizard Progress Bar -->
        <div class="wizard-progress my-3">
            <div class="wizard-step active" id="progStep1" onclick="navigateToStep(1)">
                <div class="wizard-circle"><i class="fa-solid fa-spa"></i></div>
                <div class="wizard-label">1. Treatment</div>
            </div>
            <div class="wizard-step" id="progStep2" onclick="navigateToStep(2)">
                <div class="wizard-circle"><i class="fa-regular fa-calendar-days"></i></div>
                <div class="wizard-label">2. Schedule</div>
            </div>
            <div class="wizard-step" id="progStep3" onclick="navigateToStep(3)">
                <div class="wizard-circle"><i class="fa-solid fa-users"></i></div>
                <div class="wizard-label">3. Guests</div>
            </div>
            <div class="wizard-step" id="progStep4" onclick="navigateToStep(4)">
                <div class="wizard-circle"><i class="fa-solid fa-heart-pulse"></i></div>
                <div class="wizard-label">4. Wellness</div>
            </div>
            <div class="wizard-step" id="progStep5" onclick="navigateToStep(5)">
                <div class="wizard-circle"><i class="fa-solid fa-clipboard-check"></i></div>
                <div class="wizard-label">5. Review</div>
            </div>
            <div class="wizard-step" id="progStep6">
                <div class="wizard-circle"><i class="fa-solid fa-circle-check"></i></div>
                <div class="wizard-label">6. Confirmed</div>
            </div>
        </div>

        <!-- Form Card Container -->
        <div class="card-luxury p-4 p-md-5">
            <form id="bookingForm" onsubmit="event.preventDefault(); submitBooking();">
                <?= csrf_token_field() ?>
                
                <!-- Hidden state fields -->
                <input type="hidden" name="service_id" id="selectedServiceId" value="<?= $preselectedServiceId > 0 ? $preselectedServiceId : '' ?>">
                <input type="hidden" name="service_name" id="selectedServiceName" value="">
                <input type="hidden" name="service_duration" id="selectedServiceDuration" value="60">
                <input type="hidden" name="service_base_price" id="selectedServiceBasePrice" value="0">
                <input type="hidden" name="service_max_guests" id="selectedServiceMaxGuests" value="1">
                <input type="hidden" name="service_add_fee" id="selectedServiceAddFee" value="0">
                <input type="hidden" name="service_room_type" id="selectedServiceRoomType" value="single">
                <input type="hidden" name="start_time" id="selectedStartTime" value="">
                <input type="hidden" name="end_time" id="selectedEndTime" value="">
                <input type="hidden" name="room_id" id="selectedRoomId" value="">

                <!-- ============================================================== -->
                <!-- STEP 1: SELECT SERVICE -->
                <!-- ============================================================== -->
                <div class="wizard-content-step" id="step1">
                    <div class="text-center mb-4">
                        <span class="badge badge-pending mb-2">Step 1 of 5</span>
                        <h2 class="h3 font-heading text-white">Select Your Holistic Treatment</h2>
                        <p class="text-muted small">Choose from our curated signature rituals designed to balance body and spirit.</p>
                    </div>

                    <div class="row g-4 mb-4">
                        <?php foreach ($services as $srv): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="service-select-card <?= ($preselectedServiceId === (int)$srv['id']) ? 'selected' : '' ?>"
                                     id="srvCard_<?= $srv['id'] ?>"
                                     onclick="selectService(<?= htmlspecialchars(json_encode([
                                         'id'                  => (int)$srv['id'],
                                         'name'                => $srv['name'],
                                         'duration_minutes'    => (int)$srv['duration_minutes'],
                                         'base_price'          => (float)$srv['base_price'],
                                         'max_guests'          => (int)$srv['max_guests'],
                                         'additional_guest_fee'=> (float)$srv['additional_guest_fee'],
                                         'room_type'           => $srv['room_type']
                                     ]), ENT_QUOTES) ?>)">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge" style="background: rgba(214, 183, 123, 0.15); color: var(--bvo-gold); border: 1px solid rgba(214, 183, 123, 0.3);">
                                                <?= htmlspecialchars($srv['category'], ENT_QUOTES) ?>
                                            </span>
                                            <span class="fs-5 fw-bold text-gold">
                                                <?= format_currency($srv['base_price']) ?>
                                            </span>
                                        </div>
                                        <h3 class="h5 font-heading text-white mb-2"><?= htmlspecialchars($srv['name'], ENT_QUOTES) ?></h3>
                                        <p class="text-muted small mb-3">
                                            <?= htmlspecialchars($srv['description'], ENT_QUOTES) ?>
                                        </p>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center text-muted small border-top pt-3 mb-3" style="border-color: rgba(214, 183, 123, 0.15) !important;">
                                            <span><i class="fa-regular fa-clock text-gold me-1"></i><?= $srv['duration_minutes'] ?> Mins</span>
                                            <span><i class="fa-solid fa-users text-gold me-1"></i>Up to <?= $srv['max_guests'] ?> <?= $srv['max_guests'] > 1 ? 'Guests' : 'Guest' ?></span>
                                            <span><i class="fa-solid fa-door-closed text-gold me-1"></i><?= ucfirst($srv['room_type']) ?></span>
                                        </div>
                                        <button type="button" class="btn btn-gold-outline w-100 select-btn">
                                            <span>Select Ritual</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-gold px-4" onclick="proceedToStep(2)">
                            <span>Continue to Schedule</span> <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- STEP 2: SELECT DATE & TIME -->
                <!-- ============================================================== -->
                <div class="wizard-content-step d-none" id="step2">
                    <div class="text-center mb-4">
                        <span class="badge badge-pending mb-2">Step 2 of 5</span>
                        <h2 class="h3 font-heading text-white">Select Date & Preferred Time</h2>
                        <p class="text-muted small">The sanctuary welcomes guests daily from 1:00 PM to 9:00 PM. Each session includes a 30-minute private sanitization gap.</p>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Date Picker Column -->
                        <div class="col-md-5 col-lg-4">
                            <div class="p-4 rounded-3 h-100" style="background: rgba(16, 19, 19, 0.7); border: 1px solid rgba(214, 183, 123, 0.2);">
                                <label for="appointmentDate" class="form-label form-label-luxury">
                                    <i class="fa-regular fa-calendar text-gold me-1"></i> Appointment Date
                                </label>
                                <input type="date" 
                                       id="appointmentDate" 
                                       name="appointment_date" 
                                       class="form-control form-control-luxury mb-3" 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                       value="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                       onchange="fetchAvailableSlots()">

                                <div class="p-3 rounded-2 small text-muted" style="background: rgba(23, 26, 27, 0.8); border: 1px dashed rgba(214, 183, 123, 0.25);">
                                    <h4 class="h6 font-heading text-gold mb-1">Operating Guidelines</h4>
                                    <ul class="ps-3 mb-0">
                                        <li>Hours: 1:00 PM &ndash; 9:00 PM</li>
                                        <li>Final session finishes before 9:00 PM</li>
                                        <li>30-min buffer between all bookings</li>
                                        <li>Arrival recommended 15 mins early</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Time Slots Column -->
                        <div class="col-md-7 col-lg-8">
                            <div class="p-4 rounded-3 h-100" style="background: rgba(16, 19, 19, 0.7); border: 1px solid rgba(214, 183, 123, 0.2);">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="h6 font-heading text-gold mb-0">
                                        <i class="fa-regular fa-clock me-1"></i> Available Appointment Slots
                                    </h3>
                                    <span id="slotStatusIndicator" class="small text-muted">Checking real-time availability...</span>
                                </div>

                                <!-- Slots Grid -->
                                <div id="slotsGrid" class="row g-2 mb-3">
                                    <div class="col-12 text-center py-4 text-muted">
                                        <i class="fa-solid fa-spinner fa-spin text-gold fs-4 mb-2"></i>
                                        <p class="small mb-0">Loading sanctuary appointment slots...</p>
                                    </div>
                                </div>

                                <!-- Slot Detail Summary (Shows after selection) -->
                                <div id="selectedSlotInfo" class="p-3 rounded-2 d-none" style="background: rgba(214, 183, 123, 0.08); border: 1px solid rgba(214, 183, 123, 0.3);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-gold fw-bold" id="slotDetailTime"></span>
                                            <span class="text-muted small ms-2" id="slotDetailDuration"></span>
                                        </div>
                                        <span class="badge badge-confirmed">
                                            <i class="fa-solid fa-check me-1"></i>Slot Selected
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-charcoal px-4" onclick="proceedToStep(1)">
                            <i class="fa-solid fa-arrow-left me-2"></i> <span>Back</span>
                        </button>
                        <button type="button" class="btn btn-gold px-4" onclick="proceedToStep(3)">
                            <span>Continue to Guests</span> <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- STEP 3: GUEST DETAILS & DYNAMIC PRICING -->
                <!-- ============================================================== -->
                <div class="wizard-content-step d-none" id="step3">
                    <div class="text-center mb-4">
                        <span class="badge badge-pending mb-2">Step 3 of 5</span>
                        <h2 class="h3 font-heading text-white">Party & Guest Information</h2>
                        <p class="text-muted small">Specify attendance and select an optional preferred therapist specialist.</p>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-lg-7">
                            <div class="p-4 rounded-3 h-100" style="background: rgba(16, 19, 19, 0.7); border: 1px solid rgba(214, 183, 123, 0.2);">
                                <!-- Guest Count Input -->
                                <div class="mb-4">
                                    <label for="guestCountInput" class="form-label form-label-luxury">
                                        <i class="fa-solid fa-users text-gold me-1"></i> Number of Guests
                                    </label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="number" 
                                               id="guestCountInput" 
                                               name="guest_count" 
                                               class="form-control form-control-luxury text-center fw-bold" 
                                               style="max-width: 130px; font-size: 1.25rem;" 
                                               min="1" 
                                               max="1" 
                                               value="1" 
                                               onchange="handleGuestCountChange(this.value)">
                                        <span class="text-muted small" id="guestCountHelp">Maximum 1 guest for this treatment.</span>
                                    </div>
                                </div>

                                <!-- Dynamic Extra Guests Fields Container -->
                                <div id="additionalGuestsContainer" class="mb-4 d-none">
                                    <h3 class="h6 font-heading text-gold mb-3">Additional Guest Details</h3>
                                    <div id="additionalGuestsList"></div>
                                </div>

                                <!-- Preferred Therapist Selection -->
                                <div class="mb-3">
                                    <label for="preferredTherapist" class="form-label form-label-luxury">
                                        <i class="fa-solid fa-user-nurse text-gold me-1"></i> Preferred Therapist Specialist <span class="text-muted fw-normal">(Optional)</span>
                                    </label>
                                    <select id="preferredTherapist" name="preferred_therapist_id" class="form-select form-select-luxury">
                                        <option value="">&mdash; Any Master Therapist (Auto-Assigned) &mdash;</option>
                                        <?php foreach ($therapists as $t): ?>
                                            <option value="<?= $t['id'] ?>">
                                                <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'], ENT_QUOTES) ?> &bull; 
                                                <?= htmlspecialchars($t['specialization'], ENT_QUOTES) ?> (★ <?= number_format($t['rating_avg'], 1) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted d-block mt-1">If your preferred specialist is engaged, the concierge will assign a master therapist with matching specialization.</small>
                                </div>

                                <!-- Additional Notes -->
                                <div class="mb-2">
                                    <label for="bookingNotes" class="form-label form-label-luxury">Special Requests or Comfort Preferences</label>
                                    <textarea id="bookingNotes" name="notes" rows="2" class="form-control form-control-luxury" placeholder="Room temperature, music preference, pressure level (light/medium/firm)..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Pricing Breakdown Box -->
                        <div class="col-lg-5">
                            <div class="p-4 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(16, 19, 19, 0.7); border: 1px solid rgba(214, 183, 123, 0.25);">
                                <div>
                                    <h3 class="h6 font-heading text-gold mb-3 pb-2 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                                        <i class="fa-solid fa-receipt me-1"></i> Investment Breakdown
                                    </h3>

                                    <div class="d-flex justify-content-between py-2 text-sm">
                                        <span class="text-muted" id="priceLabelBase">Base Treatment:</span>
                                        <span class="text-white fw-semibold" id="priceValBase">₱ 0.00</span>
                                    </div>

                                    <div class="d-flex justify-content-between py-2 text-sm" id="rowExtraGuests" style="display: none !important;">
                                        <span class="text-muted" id="priceLabelExtra">Additional Guests (₱750/ea):</span>
                                        <span class="text-white fw-semibold" id="priceValExtra">₱ 0.00</span>
                                    </div>

                                    <hr style="border-color: rgba(214, 183, 123, 0.2);">

                                    <div class="d-flex justify-content-between py-2">
                                        <span class="h5 font-heading text-gold mb-0">Total Amount:</span>
                                        <span class="h4 font-heading text-gold mb-0" id="priceValTotal">₱ 0.00</span>
                                    </div>

                                    <div class="p-3 mt-3 rounded-2 small" style="background: rgba(23, 26, 27, 0.9); border: 1px solid rgba(214, 183, 123, 0.15);">
                                        <span class="text-gold fw-semibold d-block mb-1">
                                            <i class="fa-solid fa-shield-halved me-1"></i>Payment & Deposit Policy
                                        </span>
                                        <p class="text-muted mb-0 small">
                                            A standard 20% downpayment confirms your suite reservation. Remaining balance may be settled upon arrival at the spa concierge.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-charcoal px-4" onclick="proceedToStep(2)">
                            <i class="fa-solid fa-arrow-left me-2"></i> <span>Back</span>
                        </button>
                        <button type="button" class="btn btn-gold px-4" onclick="proceedToStep(4)">
                            <span>Continue to Wellness</span> <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- STEP 4: HEALTH DECLARATION -->
                <!-- ============================================================== -->
                <div class="wizard-content-step d-none" id="step4">
                    <div class="text-center mb-4">
                        <span class="badge badge-pending mb-2">Step 4 of 5</span>
                        <h2 class="h3 font-heading text-white">Holistic Health & Wellness Declaration</h2>
                        <p class="text-muted small">Your safety and comfort are our sacred priority. Please declare any conditions so treatments can be adjusted safely.</p>
                    </div>

                    <div class="row justify-content-center mb-4">
                        <div class="col-lg-10">
                            <div class="p-4 rounded-3" style="background: rgba(16, 19, 19, 0.7); border: 1px solid rgba(214, 183, 123, 0.2);">
                                <!-- Medical Conditions -->
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chkMedical" name="has_medical_conditions" value="1" onchange="toggleDetailField('chkMedical', 'divMedicalDetail')">
                                        <label class="form-check-label text-white fw-semibold" for="chkMedical">
                                            Pre-existing Medical Conditions <span class="text-muted fw-normal">(High blood pressure, cardiovascular, diabetes, asthma, epilepsy, etc.)</span>
                                        </label>
                                    </div>
                                    <div id="divMedicalDetail" class="d-none ms-4">
                                        <textarea name="medical_conditions_detail" class="form-control form-control-luxury" rows="2" placeholder="Please describe conditions and medications so our therapists may adjust techniques..."></textarea>
                                    </div>
                                </div>

                                <!-- Current Injuries -->
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chkInjuries" name="has_injuries" value="1" onchange="toggleDetailField('chkInjuries', 'divInjuriesDetail')">
                                        <label class="form-check-label text-white fw-semibold" for="chkInjuries">
                                            Recent Injuries or Surgeries <span class="text-muted fw-normal">(Fractures, sprains, open wounds, recent operations within 6 months)</span>
                                        </label>
                                    </div>
                                    <div id="divInjuriesDetail" class="d-none ms-4">
                                        <textarea name="injuries_detail" class="form-control form-control-luxury" rows="2" placeholder="Detail injury locations and areas requiring avoidance or delicate handling..."></textarea>
                                    </div>
                                </div>

                                <!-- Pregnancy Status -->
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chkPregnancy" name="is_pregnant" value="1" onchange="toggleDetailField('chkPregnancy', 'divPregnancyWeeks')">
                                        <label class="form-check-label text-white fw-semibold" for="chkPregnancy">
                                            Current Pregnancy <span class="text-muted fw-normal">(Requires specialized gentle maternity bodywork; recommended after 1st trimester)</span>
                                        </label>
                                    </div>
                                    <div id="divPregnancyWeeks" class="d-none ms-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <label for="inputWeeks" class="text-muted small">Number of weeks gestation:</label>
                                            <input type="number" id="inputWeeks" name="pregnancy_weeks" class="form-control form-control-luxury" style="max-width: 120px;" min="1" max="42" placeholder="Weeks">
                                        </div>
                                    </div>
                                </div>

                                <!-- Allergies -->
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chkAllergies" name="has_allergies" value="1" onchange="toggleDetailField('chkAllergies', 'divAllergiesDetail')">
                                        <label class="form-check-label text-white fw-semibold" for="chkAllergies">
                                            Skin Allergies or Essential Oil Sensitivities <span class="text-muted fw-normal">(Nut oils, botanical extracts, synthetic aromas)</span>
                                        </label>
                                    </div>
                                    <div id="divAllergiesDetail" class="d-none ms-4">
                                        <textarea name="allergies_detail" class="form-control form-control-luxury" rows="2" placeholder="List any allergens or skin irritants to ensure hypoallergenic oil selection..."></textarea>
                                    </div>
                                </div>

                                <!-- Additional Health Notes -->
                                <div class="mb-3">
                                    <label for="healthAdditionalNotes" class="form-label form-label-luxury">Additional Wellness Concerns</label>
                                    <textarea id="healthAdditionalNotes" name="health_additional_notes" class="form-control form-control-luxury" rows="2" placeholder="Any other physiological considerations you would like your therapist to know..."></textarea>
                                </div>

                                <!-- Privacy Notice in accordance with RA 10173 -->
                                <div class="p-3 rounded-2 small text-muted" style="background: rgba(23, 26, 27, 0.9); border: 1px solid rgba(214, 183, 123, 0.15);">
                                    <i class="fa-solid fa-lock text-gold me-1"></i>
                                    <strong>Confidentiality Notice (Philippine Data Privacy Act of 2012 / RA 10173):</strong>
                                    All health declarations are strictly confidential, protected under medical-grade privacy standards, and accessible exclusively to your assigned therapist and senior concierge to ensure treatment suitability.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-charcoal px-4" onclick="proceedToStep(3)">
                            <i class="fa-solid fa-arrow-left me-2"></i> <span>Back</span>
                        </button>
                        <button type="button" class="btn btn-gold px-4" onclick="proceedToStep(5)">
                            <span>Review Booking</span> <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- STEP 5: REVIEW & CONFIRM -->
                <!-- ============================================================== -->
                <div class="wizard-content-step d-none" id="step5">
                    <div class="text-center mb-4">
                        <span class="badge badge-pending mb-2">Step 5 of 5</span>
                        <h2 class="h3 font-heading text-white">Review & Confirm Your Reservation</h2>
                        <p class="text-muted small">Please verify all reservation details before confirming your sanctuary appointment.</p>
                    </div>

                    <div class="row justify-content-center mb-4">
                        <div class="col-lg-10">
                            <div class="p-4 rounded-3" style="background: rgba(16, 19, 19, 0.8); border: 1px solid rgba(214, 183, 123, 0.3);">
                                <h3 class="h5 font-heading text-gold mb-3 pb-2 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                                    Booking Summary
                                </h3>

                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Selected Treatment:</span>
                                        <strong class="text-white fs-5 font-heading" id="revServiceName"></strong>
                                        <div class="text-muted small" id="revServiceDuration"></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Schedule Date & Time:</span>
                                        <strong class="text-gold" id="revDateTime"></strong>
                                        <div class="text-muted small" id="revRoomType"></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Party Size:</span>
                                        <strong class="text-white" id="revGuests"></strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted small d-block">Therapist Specialist:</span>
                                        <strong class="text-white" id="revTherapist"></strong>
                                    </div>
                                </div>

                                <div class="p-3 rounded-2 mb-4" style="background: rgba(23, 26, 27, 0.9); border: 1px solid rgba(214, 183, 123, 0.2);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h6 font-heading text-white mb-0">Total Treatment Investment:</span>
                                        <span class="h3 font-heading text-gold mb-0" id="revTotalAmount">₱ 0.00</span>
                                    </div>
                                </div>

                                <!-- Cancellation & Policy Reminders -->
                                <div class="p-3 rounded-2 mb-4 small" style="background: rgba(214, 183, 123, 0.05); border: 1px solid rgba(214, 183, 123, 0.25);">
                                    <h4 class="h6 font-heading text-gold mb-2">
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Cancellation & Rescheduling Policy (6-Hour Notice)
                                    </h4>
                                    <p class="text-muted mb-1">
                                        We appreciate that schedules may change. You may reschedule or cancel your reservation without penalty up to <strong>6 hours before</strong> your scheduled appointment time.
                                    </p>
                                    <p class="text-muted mb-0">
                                        Cancellations within 6 hours of the session may result in the forfeiture of the deposit according to standard sanctuary policy.
                                    </p>
                                </div>

                                <!-- Terms & Conditions Checkbox -->
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="termsAccepted" name="terms_accepted" value="1" required>
                                    <label class="form-check-label text-white small" for="termsAccepted">
                                        I have reviewed and agree to the <a href="../terms.php" target="_blank" class="text-gold text-decoration-underline">Terms & Conditions</a>, 
                                        the 6-hour cancellation policy, and acknowledge the accurate completion of the health declaration.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-charcoal px-4" onclick="proceedToStep(4)">
                            <i class="fa-solid fa-arrow-left me-2"></i> <span>Back</span>
                        </button>
                        <button type="submit" class="btn btn-gold px-5" id="btnSubmitBooking">
                            <i class="fa-solid fa-shield-check me-2"></i> <span>Confirm Reservation</span>
                        </button>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- STEP 6: CONFIRMATION SCREEN (Shown after AJAX success) -->
                <!-- ============================================================== -->
                <div class="wizard-content-step d-none" id="step6">
                    <div class="text-center py-4">
                        <div class="stat-icon mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem; background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4); color: #34D399;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <span class="badge badge-confirmed mb-2">Reservation Recorded</span>
                        <h2 class="h2 font-heading text-white mb-2">Your Sanctuary Slot is Reserved</h2>
                        <p class="text-muted small mb-4" style="max-width: 500px; margin: 0 auto;">
                            We have prepared your luxury treatment experience. Please take note of your official reference number.
                        </p>

                        <div class="p-4 rounded-3 text-start mx-auto mb-4" style="max-width: 550px; background: rgba(16, 19, 19, 0.85); border: 1px solid rgba(214, 183, 123, 0.3);">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                                <span class="text-muted small">Booking Reference:</span>
                                <span class="fs-5 fw-bold font-monospace text-gold" id="confReferenceNumber">BVO-00000</span>
                            </div>
                            <div class="row g-2 small text-muted mb-3">
                                <div class="col-6">
                                    <span>Treatment:</span>
                                    <strong class="d-block text-white" id="confServiceName"></strong>
                                </div>
                                <div class="col-6">
                                    <span>Date & Time:</span>
                                    <strong class="d-block text-gold" id="confDateTime"></strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span>Total Investment:</span>
                                    <strong class="d-block text-white" id="confTotal"></strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span>Status:</span>
                                    <span class="badge-status badge-pending d-inline-flex mt-1">Pending Payment</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-2 text-center small text-muted" style="background: rgba(23, 26, 27, 0.9);">
                                A confirmation notice has been dispatched to your notification inbox.
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="#" id="confPayBtn" class="btn btn-gold px-4">
                                <i class="fa-solid fa-credit-card me-2"></i> Proceed to Payment
                            </a>
                            <a href="appointments.php" class="btn btn-gold-outline px-4">
                                <i class="fa-solid fa-clock-rotate-left me-2"></i> Pay Later & View Bookings
                            </a>
                            <button type="button" class="btn btn-charcoal px-3" onclick="window.print()">
                                <i class="fa-solid fa-print me-2"></i> Print Summary
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Step Wizard & Interactive Logic -->
<script>
let currentStep = 1;
let selectedServiceData = null;
let selectedSlotData = null;

document.addEventListener('DOMContentLoaded', function() {
    <?php if ($preselectedServiceId > 0): ?>
        const preCard = document.getElementById('srvCard_<?= $preselectedServiceId ?>');
        if (preCard) {
            preCard.click();
        }
    <?php endif; ?>
});

function selectService(service) {
    selectedServiceData = service;
    
    // Update hidden inputs
    document.getElementById('selectedServiceId').value = service.id;
    document.getElementById('selectedServiceName').value = service.name;
    document.getElementById('selectedServiceDuration').value = service.duration_minutes;
    document.getElementById('selectedServiceBasePrice').value = service.base_price;
    document.getElementById('selectedServiceMaxGuests').value = service.max_guests;
    document.getElementById('selectedServiceAddFee').value = service.additional_guest_fee;
    document.getElementById('selectedServiceRoomType').value = service.room_type;

    // Highlight card
    document.querySelectorAll('.service-select-card').forEach(c => c.classList.remove('selected'));
    const targetCard = document.getElementById('srvCard_' + service.id);
    if (targetCard) {
        targetCard.classList.add('selected');
    }

    // Configure guest count limit in step 3
    const guestInput = document.getElementById('guestCountInput');
    const guestHelp = document.getElementById('guestCountHelp');
    guestInput.max = service.max_guests;
    if (parseInt(guestInput.value) > service.max_guests) {
        guestInput.value = service.max_guests;
    }
    guestHelp.innerText = `Accommodates 1 to ${service.max_guests} guests for this experience.`;

    // Reset slot selection when service changes
    selectedSlotData = null;
    document.getElementById('selectedStartTime').value = '';
    document.getElementById('selectedEndTime').value = '';
    document.getElementById('selectedRoomId').value = '';
    const slotInfo = document.getElementById('selectedSlotInfo');
    if (slotInfo) slotInfo.classList.add('d-none');

    // Recalculate price
    calculatePricing();
}

function fetchAvailableSlots() {
    const serviceId = document.getElementById('selectedServiceId').value;
    const date = document.getElementById('appointmentDate').value;
    const grid = document.getElementById('slotsGrid');
    const indicator = document.getElementById('slotStatusIndicator');

    if (!serviceId) {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-muted"><p>Please select a treatment first.</p></div>';
        return;
    }

    if (!date) {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-muted"><p>Please choose an appointment date.</p></div>';
        return;
    }

    indicator.innerText = 'Checking availability...';
    grid.innerHTML = '<div class="col-12 text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin text-gold fs-4 mb-2"></i><p class="small mb-0">Querying sanctuary suite schedule...</p></div>';

    fetch(`../process/get_time_slots.php?date=${encodeURIComponent(date)}&service_id=${encodeURIComponent(serviceId)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                indicator.innerText = 'Availability check issue';
                grid.innerHTML = `<div class="col-12 text-center py-4 text-danger"><p class="small">${data.message}</p></div>`;
                return;
            }

            indicator.innerText = `${data.available_count} slots open`;
            grid.innerHTML = '';

            if (data.slots.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-4 text-muted"><p class="small">No operating slots fit before closing on this date.</p></div>';
                return;
            }

            data.slots.forEach(slot => {
                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4 col-md-3';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `slot-btn w-100 ${slot.is_available ? '' : 'disabled'}`;
                btn.disabled = !slot.is_available;
                btn.title = slot.reason;
                btn.innerHTML = `<span class="d-block fw-semibold">${slot.display}</span><span class="small opacity-75" style="font-size: 0.72rem;">until ${slot.display_end}</span>`;

                if (selectedSlotData && selectedSlotData.start_time === slot.start_time_raw) {
                    btn.classList.add('selected');
                }

                if (slot.is_available) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');
                        selectSlot(slot);
                    });
                }

                col.appendChild(btn);
                grid.appendChild(col);
            });
        })
        .catch(err => {
            indicator.innerText = 'Error loading slots';
            grid.innerHTML = '<div class="col-12 text-center py-4 text-danger"><p class="small">Could not load time slots. Please try again.</p></div>';
        });
}

function selectSlot(slot) {
    selectedSlotData = slot;
    document.getElementById('selectedStartTime').value = slot.start_time_raw;
    document.getElementById('selectedEndTime').value = slot.end_time + ':00';

    if (slot.available_rooms && slot.available_rooms.length > 0) {
        document.getElementById('selectedRoomId').value = slot.available_rooms[0].id;
    }

    const slotInfo = document.getElementById('selectedSlotInfo');
    const slotDetailTime = document.getElementById('slotDetailTime');
    const slotDetailDuration = document.getElementById('slotDetailDuration');

    slotDetailTime.innerText = `${slot.display} – ${slot.display_end}`;
    slotDetailDuration.innerText = `(${selectedServiceData ? selectedServiceData.duration_minutes : 60} mins treatment + 30m clean)`;
    slotInfo.classList.remove('d-none');
}

function handleGuestCountChange(count) {
    count = parseInt(count) || 1;
    const max = selectedServiceData ? selectedServiceData.max_guests : 1;
    if (count > max) {
        count = max;
        document.getElementById('guestCountInput').value = max;
    }
    if (count < 1) {
        count = 1;
        document.getElementById('guestCountInput').value = 1;
    }

    const container = document.getElementById('additionalGuestsContainer');
    const list = document.getElementById('additionalGuestsList');
    list.innerHTML = '';

    if (count > 1) {
        container.classList.remove('d-none');
        for (let i = 2; i <= count; i++) {
            const guestDiv = document.createElement('div');
            guestDiv.className = 'p-3 mb-2 rounded-2';
            guestDiv.style.background = 'rgba(23, 26, 27, 0.8)';
            guestDiv.style.border = '1px solid rgba(214, 183, 123, 0.15)';
            guestDiv.innerHTML = `
                <span class="badge badge-pending mb-2">Guest #${i} Details</span>
                <div class="row g-2">
                    <div class="col-sm-5">
                        <input type="text" name="guests[${i}][name]" class="form-control form-control-luxury" placeholder="Full Name" required>
                    </div>
                    <div class="col-sm-4">
                        <input type="email" name="guests[${i}][email]" class="form-control form-control-luxury" placeholder="Email (Optional)">
                    </div>
                    <div class="col-sm-3">
                        <input type="tel" name="guests[${i}][phone]" class="form-control form-control-luxury" placeholder="Phone (Optional)">
                    </div>
                </div>
            `;
            list.appendChild(guestDiv);
        }
    } else {
        container.classList.add('d-none');
    }

    calculatePricing();
}

function calculatePricing() {
    if (!selectedServiceData) return;

    const basePrice = parseFloat(selectedServiceData.base_price) || 0;
    const addFee = parseFloat(selectedServiceData.additional_guest_fee) || 0;
    const guestCount = parseInt(document.getElementById('guestCountInput').value) || 1;
    const roomType = selectedServiceData.room_type;

    let extraCharges = 0;
    if (roomType === 'family' && guestCount > 4) {
        const extraGuests = guestCount - 4;
        extraCharges = extraGuests * addFee;
    }

    const total = basePrice + extraCharges;

    document.getElementById('priceValBase').innerText = '₱ ' + basePrice.toLocaleString('en-US', {minimumFractionDigits: 2});
    
    const rowExtra = document.getElementById('rowExtraGuests');
    if (extraCharges > 0) {
        rowExtra.style.display = 'flex';
        rowExtra.style.setProperty('display', 'flex', 'important');
        document.getElementById('priceValExtra').innerText = '₱ ' + extraCharges.toLocaleString('en-US', {minimumFractionDigits: 2});
    } else {
        rowExtra.style.display = 'none';
        rowExtra.style.setProperty('display', 'none', 'important');
    }

    document.getElementById('priceValTotal').innerText = '₱ ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
}

function toggleDetailField(checkboxId, targetDivId) {
    const chk = document.getElementById(checkboxId);
    const target = document.getElementById(targetDivId);
    if (chk && target) {
        if (chk.checked) {
            target.classList.remove('d-none');
        } else {
            target.classList.add('d-none');
        }
    }
}

function proceedToStep(targetStep) {
    // Step Validations
    if (targetStep === 2) {
        if (!selectedServiceData) {
            alert('Please select a luxury treatment first.');
            return;
        }
        fetchAvailableSlots();
    }

    if (targetStep === 3) {
        if (!document.getElementById('appointmentDate').value) {
            alert('Please pick an appointment date.');
            return;
        }
        if (!document.getElementById('selectedStartTime').value) {
            alert('Please select an available appointment time slot.');
            return;
        }
    }

    if (targetStep === 5) {
        // Populate Review Summary
        document.getElementById('revServiceName').innerText = selectedServiceData.name;
        document.getElementById('revServiceDuration').innerText = `${selectedServiceData.duration_minutes} minutes sanctuary ritual`;
        
        const dateVal = document.getElementById('appointmentDate').value;
        const timeVal = selectedSlotData ? `${selectedSlotData.display} – ${selectedSlotData.display_end}` : '';
        document.getElementById('revDateTime').innerText = `${dateVal} at ${timeVal}`;
        document.getElementById('revRoomType').innerText = `Suite Type: ${selectedServiceData.room_type.toUpperCase()}`;

        const guests = document.getElementById('guestCountInput').value;
        document.getElementById('revGuests').innerText = `${guests} ${guests > 1 ? 'Guests' : 'Guest'}`;

        const therapistSelect = document.getElementById('preferredTherapist');
        const therapistText = therapistSelect.options[therapistSelect.selectedIndex].text;
        document.getElementById('revTherapist').innerText = therapistText;

        document.getElementById('revTotalAmount').innerText = document.getElementById('priceValTotal').innerText;
    }

    // Switch view
    document.querySelectorAll('.wizard-content-step').forEach(s => s.classList.add('d-none'));
    const targetView = document.getElementById('step' + targetStep);
    if (targetView) {
        targetView.classList.remove('d-none');
    }

    // Update progress bar
    for (let i = 1; i <= 6; i++) {
        const stepElem = document.getElementById('progStep' + i);
        if (stepElem) {
            stepElem.classList.remove('active', 'completed');
            if (i < targetStep) {
                stepElem.classList.add('completed');
            } else if (i === targetStep) {
                stepElem.classList.add('active');
            }
        }
    }

    currentStep = targetStep;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function navigateToStep(step) {
    if (step < currentStep) {
        proceedToStep(step);
    }
}

function submitBooking() {
    const terms = document.getElementById('termsAccepted');
    if (!terms.checked) {
        alert('Please accept the Terms & Conditions and Cancellation Policy to complete your reservation.');
        return;
    }

    const btnSubmit = document.getElementById('btnSubmitBooking');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Securing Reservation...';

    const formData = new FormData(document.getElementById('bookingForm'));

    fetch('../process/book_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Could not complete reservation.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-shield-check me-2"></i> Confirm Reservation';
            return;
        }

        // Show Confirmation Step 6
        document.getElementById('confReferenceNumber').innerText = data.reference;
        document.getElementById('confServiceName').innerText = selectedServiceData.name;
        document.getElementById('confDateTime').innerText = document.getElementById('revDateTime').innerText;
        document.getElementById('confTotal').innerText = document.getElementById('priceValTotal').innerText;
        document.getElementById('confPayBtn').href = `pay.php?appointment_id=${data.appointment_id}`;

        proceedToStep(6);
    })
    .catch(err => {
        alert('An unexpected server communication error occurred.');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-shield-check me-2"></i> Confirm Reservation';
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
