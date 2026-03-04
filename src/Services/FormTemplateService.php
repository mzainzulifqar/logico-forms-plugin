<?php

namespace Logicoforms\Forms\Services;

class FormTemplateService
{
    public static function all(): array
    {
        return [
            // ── Feedback ─────────────────────────────────────────

            // Q0 rating → Q1 frequency → Q2 recommend? branch:
            //   Very likely/Somewhat likely → Q3 what do you like → always→Q5
            //   Not likely/Not at all → Q4 biggest disappointment
            // Q5 improvements → Q6 compare to competitors? branch:
            //   Much better/Somewhat better → Q8 email
            //   About the same/Worse → Q7 what do competitors do better
            // Q7 → Q8 email
            [
                'slug' => 'customer-feedback',
                'title' => 'Customer Feedback Survey',
                'description' => 'Collect feedback from customers about your product or service.',
                'category' => 'feedback',
                'icon' => 'star',
                'theme' => ['background_color' => '#0445AF', 'question_color' => '#FFFFFF', 'answer_color' => '#93C5FD', 'button_color' => '#FFFFFF', 'button_text_color' => '#0445AF', 'font' => 'Inter', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'rating', 'question_text' => 'How would you rate your overall experience?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'radio', 'question_text' => 'How often do you use our product?', 'is_required' => true, 'options' => ['Daily', 'Weekly', 'Monthly', 'Rarely']],
                    ['type' => 'radio', 'question_text' => 'How likely are you to recommend us to a friend?', 'is_required' => true, 'options' => ['Very likely', 'Somewhat likely', 'Not likely', 'Not at all'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Very likely', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'Somewhat likely', 'next' => 3],
                        ],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'What do you like most about our product?', 'is_required' => true, 'options' => ['Ease of use', 'Features', 'Customer support', 'Price', 'Reliability'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'text', 'question_text' => 'What was the biggest disappointment with our product?', 'is_required' => true, 'settings' => ['placeholder' => 'Tell us what went wrong...']],
                    ['type' => 'checkbox', 'question_text' => 'Which areas should we improve?', 'options' => ['User interface', 'Performance', 'Documentation', 'Pricing', 'Support response time']],
                    ['type' => 'radio', 'question_text' => 'How does our product compare to alternatives you\'ve tried?', 'is_required' => true, 'options' => ['Much better', 'Somewhat better', 'About the same', 'Worse'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Much better', 'next' => 8],
                            ['operator' => 'equals', 'value' => 'Somewhat better', 'next' => 8],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What do competitors do better than us?', 'is_required' => true, 'settings' => ['placeholder' => 'Features, pricing, support...']],
                    ['type' => 'email', 'question_text' => 'Your email (optional, if you\'d like us to follow up)', 'settings' => ['placeholder' => 'you@example.com']],
                ],
            ],

            // Q0 what brought you → Q1 navigation rating → Q2 found info? branch:
            //   Yes, easily → Q3 rate design → always→Q6
            //   Yes, with some effort → Q4 what was hard → always→Q6
            //   No → Q5 what were you looking for
            // Q5 → Q6 visit again? → branch:
            //   Definitely/Probably → Q8 email
            //   Not sure/No → Q7 what would bring you back
            // Q7 → Q8
            [
                'slug' => 'website-feedback',
                'title' => 'Website Feedback',
                'description' => 'Get visitor feedback on your website experience.',
                'category' => 'feedback',
                'icon' => 'globe',
                'theme' => ['background_color' => '#0C1929', 'question_color' => '#E2E8F0', 'answer_color' => '#06B6D4', 'button_color' => '#06B6D4', 'button_text_color' => '#0C1929', 'font' => 'Inter', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'radio', 'question_text' => 'What brought you to our website today?', 'is_required' => true, 'options' => ['Search engine', 'Social media', 'Referral', 'Advertisement', 'Direct link']],
                    ['type' => 'rating', 'question_text' => 'How easy was it to navigate our website?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'radio', 'question_text' => 'Did you find the information you were looking for?', 'is_required' => true, 'options' => ['Yes, easily', 'Yes, with some effort', 'No'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Yes, easily', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'Yes, with some effort', 'next' => 4],
                        ],
                    ],
                    ['type' => 'rating', 'question_text' => 'How would you rate the visual design?', 'is_required' => true, 'settings' => ['max' => 5],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'text', 'question_text' => 'What was difficult to find?', 'is_required' => true, 'settings' => ['placeholder' => 'Describe what you struggled with...'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'text', 'question_text' => 'What were you looking for that you couldn\'t find?', 'is_required' => true, 'settings' => ['placeholder' => 'Describe what was missing...']],
                    ['type' => 'radio', 'question_text' => 'Would you visit our website again?', 'is_required' => true, 'options' => ['Definitely', 'Probably', 'Not sure', 'No'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Definitely', 'next' => 8],
                            ['operator' => 'equals', 'value' => 'Probably', 'next' => 8],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What would need to change for you to come back?', 'is_required' => true, 'settings' => ['placeholder' => 'Your suggestions...']],
                    ['type' => 'email', 'question_text' => 'Your email (optional)', 'settings' => ['placeholder' => 'you@example.com']],
                ],
            ],

            // ── Registration ─────────────────────────────────────

            // Q0 name → Q1 email → Q2 ticket type? branch:
            //   General Admission → Q3 (workshop?) → default Q6
            //   VIP → Q4 (backstage? + plus-one) → always→Q6
            //   Student → Q5 (student ID) → default Q6
            // Q6 guests → Q7 dietary → Q8 accessibility → Q9 special requests
            [
                'slug' => 'event-registration',
                'title' => 'Event Registration',
                'description' => 'Register attendees for your event with ticket selection and details.',
                'category' => 'registration',
                'icon' => 'calendar',
                'theme' => ['background_color' => '#4F46E5', 'question_color' => '#FFFFFF', 'answer_color' => '#C7D2FE', 'button_color' => '#FFFFFF', 'button_text_color' => '#4F46E5', 'font' => 'Inter', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'What is your full name?', 'is_required' => true, 'settings' => ['placeholder' => 'John Doe']],
                    ['type' => 'email', 'question_text' => 'What is your email address?', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'radio', 'question_text' => 'Which ticket type would you like?', 'is_required' => true, 'options' => ['General Admission', 'VIP', 'Student'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'VIP', 'next' => 4],
                            ['operator' => 'equals', 'value' => 'Student', 'next' => 5],
                        ],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'Which workshops would you like to attend?', 'options' => ['Morning keynote', 'Afternoon breakout A', 'Afternoon breakout B', 'Evening networking'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'radio', 'question_text' => 'VIP perks — would you like backstage access and a plus-one?', 'is_required' => true, 'options' => ['Backstage + plus-one', 'Backstage only', 'Neither'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'text', 'question_text' => 'Please provide your student ID number', 'is_required' => true, 'settings' => ['placeholder' => 'e.g. STU-12345']],
                    ['type' => 'number', 'question_text' => 'How many additional guests will you bring?', 'settings' => ['placeholder' => '0']],
                    ['type' => 'checkbox', 'question_text' => 'Any dietary requirements?', 'options' => ['Vegetarian', 'Vegan', 'Gluten-free', 'Halal', 'None']],
                    ['type' => 'radio', 'question_text' => 'Do you have any accessibility needs?', 'options' => ['Wheelchair access', 'Sign language interpreter', 'Hearing loop', 'None'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Wheelchair access', 'next' => 9],
                            ['operator' => 'equals', 'value' => 'Sign language interpreter', 'next' => 9],
                            ['operator' => 'equals', 'value' => 'Hearing loop', 'next' => 9],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'Please describe your accessibility requirements', 'is_required' => true, 'settings' => ['placeholder' => 'So we can make proper arrangements...']],
                    ['type' => 'text', 'question_text' => 'Any other special requests?', 'settings' => ['placeholder' => 'Let us know...']],
                ],
            ],

            // Q0 name → Q1 email → Q2 course? branch:
            //   Intro to Design → Q3 design experience
            //   Advanced Marketing → Q4 marketing background
            //   Data Analytics → Q5 analytics tools
            //   Project Management → default Q6
            // Q3,Q4,Q5 → always→Q6
            // Q6 skill level → Q7 schedule → Q8 goals → Q9 certificate
            [
                'slug' => 'course-enrollment',
                'title' => 'Course Enrollment',
                'description' => 'Enroll students in your course or training program.',
                'category' => 'registration',
                'icon' => 'book',
                'theme' => ['background_color' => '#064E3B', 'question_color' => '#ECFDF5', 'answer_color' => '#6EE7B7', 'button_color' => '#10B981', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'What is your full name?', 'is_required' => true, 'settings' => ['placeholder' => 'Jane Smith']],
                    ['type' => 'email', 'question_text' => 'Your email address', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'radio', 'question_text' => 'Which course are you enrolling in?', 'is_required' => true, 'options' => ['Introduction to Design', 'Advanced Marketing', 'Data Analytics', 'Project Management'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Introduction to Design', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'Advanced Marketing', 'next' => 4],
                            ['operator' => 'equals', 'value' => 'Data Analytics', 'next' => 5],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Do you have any prior design experience?', 'is_required' => true, 'options' => ['None', 'Self-taught', 'Formal education', 'Professional experience'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'radio', 'question_text' => 'How many years of marketing experience do you have?', 'is_required' => true, 'options' => ['Less than 1 year', '1-3 years', '3-5 years', '5+ years'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'Which analytics tools have you used before?', 'options' => ['Excel', 'Google Analytics', 'SQL', 'Python/R', 'Tableau', 'None'],
                    ],
                    ['type' => 'radio', 'question_text' => 'What is your current skill level in this subject?', 'is_required' => true, 'options' => ['Beginner', 'Intermediate', 'Advanced']],
                    ['type' => 'radio', 'question_text' => 'Preferred schedule', 'is_required' => true, 'options' => ['Weekday mornings', 'Weekday evenings', 'Weekends']],
                    ['type' => 'text', 'question_text' => 'What do you hope to achieve from this course?', 'settings' => ['placeholder' => 'Your learning goals...']],
                    ['type' => 'radio', 'question_text' => 'Do you need a certificate of completion?', 'options' => ['Yes', 'No']],
                ],
            ],

            // ── HR ───────────────────────────────────────────────

            // Q0 name → Q1 email → Q2 position? → Q3 experience? branch:
            //   0-1 years → Q4 education → always→Q8
            //   2-4 / 5-9 years → Q5 best project → Q6 team size → always→Q8
            //   10+ years → Q7 leadership
            // Q7 → Q8 work arrangement → Q9 start date → Q10 anything else
            [
                'slug' => 'job-application',
                'title' => 'Job Application',
                'description' => 'Collect job applications with experience-based branching.',
                'category' => 'hr',
                'icon' => 'briefcase',
                'theme' => ['background_color' => '#18181B', 'question_color' => '#FAFAFA', 'answer_color' => '#A78BFA', 'button_color' => '#8B5CF6', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'What is your full name?', 'is_required' => true, 'settings' => ['placeholder' => 'John Doe']],
                    ['type' => 'email', 'question_text' => 'Your email address', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'radio', 'question_text' => 'Which position are you applying for?', 'is_required' => true, 'options' => ['Software Engineer', 'Product Designer', 'Marketing Manager', 'Sales Representative']],
                    ['type' => 'radio', 'question_text' => 'Years of professional experience', 'is_required' => true, 'options' => ['0-1 years', '2-4 years', '5-9 years', '10+ years'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => '0-1 years', 'next' => 4],
                            ['operator' => 'equals', 'value' => '2-4 years', 'next' => 5],
                            ['operator' => 'equals', 'value' => '5-9 years', 'next' => 5],
                            ['operator' => 'equals', 'value' => '10+ years', 'next' => 7],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What relevant education or training do you have?', 'is_required' => true, 'settings' => ['placeholder' => 'Degree, bootcamp, certifications...'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 8]],
                    ],
                    ['type' => 'text', 'question_text' => 'Describe your most relevant project or achievement', 'is_required' => true, 'settings' => ['placeholder' => 'Tell us about a project you\'re proud of...']],
                    ['type' => 'radio', 'question_text' => 'What was the largest team you worked on?', 'is_required' => true, 'options' => ['Solo', '2-5 people', '6-15 people', '15+ people'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 8]],
                    ],
                    ['type' => 'text', 'question_text' => 'Describe your leadership and management experience', 'is_required' => true, 'settings' => ['placeholder' => 'Teams managed, scope of responsibility...']],
                    ['type' => 'radio', 'question_text' => 'What is your preferred work arrangement?', 'is_required' => true, 'options' => ['Remote', 'Hybrid', 'On-site'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Remote', 'next' => 9],
                            ['operator' => 'equals', 'value' => 'On-site', 'next' => 10],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Which timezone are you based in?', 'is_required' => true, 'options' => ['US Eastern', 'US Pacific', 'Europe', 'Asia', 'Other'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 11]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Are you willing to relocate if needed?', 'options' => ['Yes', 'Depends on location', 'No'],
                    ],
                    ['type' => 'radio', 'question_text' => 'When can you start?', 'is_required' => true, 'options' => ['Immediately', 'Within 2 weeks', 'Within a month', 'Other']],
                    ['type' => 'text', 'question_text' => 'Anything else you\'d like us to know?', 'settings' => ['placeholder' => 'Links to portfolio, LinkedIn, etc.']],
                ],
            ],

            // Q0 department? branch:
            //   Engineering → Q1 rate dev tools → always→Q3
            //   Sales → Q2 rate sales enablement
            //   Other (Marketing, Operations) → default Q3
            // Q3 overall satisfaction → Q4 work-life balance → Q5 recognized? branch:
            //   Agree/Strongly agree → Q7
            //   Neutral/Disagree/Strongly disagree → Q6 what recognition
            // Q6 → Q7 recommend? branch:
            //   Definitely/Probably → Q9
            //   Not sure/Probably not/Definitely not → Q8 what would change
            // Q8 → Q9 other feedback
            [
                'slug' => 'employee-satisfaction',
                'title' => 'Employee Satisfaction Survey',
                'description' => 'Measure employee satisfaction and workplace culture.',
                'category' => 'hr',
                'icon' => 'heart',
                'theme' => ['background_color' => '#FFFBF5', 'question_color' => '#1F2937', 'answer_color' => '#EA580C', 'button_color' => '#EA580C', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'radio', 'question_text' => 'Which department do you work in?', 'is_required' => true, 'options' => ['Engineering', 'Sales', 'Marketing', 'Operations'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Engineering', 'next' => 1],
                            ['operator' => 'equals', 'value' => 'Sales', 'next' => 2],
                        ],
                    ],
                    ['type' => 'rating', 'question_text' => 'How would you rate your development tools and infrastructure?', 'is_required' => true, 'settings' => ['max' => 5],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 3]],
                    ],
                    ['type' => 'rating', 'question_text' => 'How would you rate your sales enablement tools and CRM?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'rating', 'question_text' => 'How satisfied are you with your job overall?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'rating', 'question_text' => 'How would you rate your work-life balance?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'radio', 'question_text' => 'Do you feel your work is recognized and valued?', 'is_required' => true, 'options' => ['Strongly agree', 'Agree', 'Neutral', 'Disagree', 'Strongly disagree'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Strongly agree', 'next' => 7],
                            ['operator' => 'equals', 'value' => 'Agree', 'next' => 7],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What kind of recognition would be most meaningful to you?', 'is_required' => true, 'settings' => ['placeholder' => 'Public praise, bonuses, growth opportunities...']],
                    ['type' => 'radio', 'question_text' => 'Would you recommend this company as a place to work?', 'is_required' => true, 'options' => ['Definitely', 'Probably', 'Not sure', 'Probably not', 'Definitely not'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Definitely', 'next' => 9],
                            ['operator' => 'equals', 'value' => 'Probably', 'next' => 9],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What would need to change for you to recommend us?', 'is_required' => true, 'settings' => ['placeholder' => 'Be specific about what would improve things...']],
                    ['type' => 'text', 'question_text' => 'Any other feedback or suggestions?', 'settings' => ['placeholder' => 'Share your thoughts...']],
                ],
            ],

            // ── Order ────────────────────────────────────────────

            // Q0 name → Q1 email → Q2 product? branch:
            //   Basic Package → Q3 (basic config)
            //   Standard Package → Q4 (standard add-ons)
            //   Premium Package → Q5 (premium customization)
            // Q3,Q4,Q5 → always→Q6
            // Q6 quantity → Q7 delivery method? branch:
            //   Standard/Express shipping → Q8 shipping address → Q9 phone
            //   Local pickup → Q10 pickup time
            // Q9 → always→Q11, Q10 → Q11
            // Q11 order notes
            [
                'slug' => 'product-order',
                'title' => 'Product Order Form',
                'description' => 'Accept product orders with delivery method selection.',
                'category' => 'order',
                'icon' => 'cart',
                'theme' => ['background_color' => '#FFFFFF', 'question_color' => '#18181B', 'answer_color' => '#F97316', 'button_color' => '#F97316', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'Your full name', 'is_required' => true, 'settings' => ['placeholder' => 'John Doe']],
                    ['type' => 'email', 'question_text' => 'Your email address', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'radio', 'question_text' => 'Which product would you like to order?', 'is_required' => true, 'options' => ['Basic Package', 'Standard Package', 'Premium Package'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Basic Package', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'Standard Package', 'next' => 4],
                            ['operator' => 'equals', 'value' => 'Premium Package', 'next' => 5],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Choose your Basic Package color', 'is_required' => true, 'options' => ['White', 'Black', 'Gray'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'Select Standard Package add-ons', 'options' => ['Extended warranty', 'Gift wrapping', 'Priority support', 'None'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'text', 'question_text' => 'Describe your Premium Package customization', 'is_required' => true, 'settings' => ['placeholder' => 'Color, engraving, special features...']],
                    ['type' => 'number', 'question_text' => 'Quantity', 'is_required' => true, 'settings' => ['placeholder' => '1']],
                    ['type' => 'radio', 'question_text' => 'Delivery method', 'is_required' => true, 'options' => ['Standard shipping', 'Express shipping', 'Local pickup'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Standard shipping', 'next' => 8],
                            ['operator' => 'equals', 'value' => 'Express shipping', 'next' => 8],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'Shipping address', 'is_required' => true, 'settings' => ['placeholder' => 'Street, city, state, ZIP']],
                    ['type' => 'text', 'question_text' => 'Phone number for delivery updates', 'is_required' => true, 'settings' => ['placeholder' => '+1 (555) 000-0000'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 11]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Preferred pickup time', 'is_required' => true, 'options' => ['Morning (9am-12pm)', 'Afternoon (12pm-5pm)', 'Evening (5pm-8pm)']],
                    ['type' => 'text', 'question_text' => 'Order notes or special instructions', 'settings' => ['placeholder' => 'Any special requests...']],
                ],
            ],

            // Q0 name → Q1 email → Q2 guests → Q3 occasion? branch:
            //   Birthday → Q4 surprise? → always→Q6
            //   Business dinner → Q5 private room/AV
            //   Anniversary/Just dining → default Q6
            // Q6 time slot → Q7 seating → Q8 dietary → Q9 requests
            [
                'slug' => 'restaurant-reservation',
                'title' => 'Restaurant Reservation',
                'description' => 'Let guests book a table at your restaurant.',
                'category' => 'order',
                'icon' => 'utensils',
                'theme' => ['background_color' => '#1C1917', 'question_color' => '#FAFAF9', 'answer_color' => '#FBBF24', 'button_color' => '#FBBF24', 'button_text_color' => '#1C1917', 'font' => 'Georgia', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'Name for the reservation', 'is_required' => true, 'settings' => ['placeholder' => 'John Doe']],
                    ['type' => 'email', 'question_text' => 'Email address', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'number', 'question_text' => 'Number of guests', 'is_required' => true, 'settings' => ['placeholder' => '2']],
                    ['type' => 'radio', 'question_text' => 'Is this for a special occasion?', 'is_required' => true, 'options' => ['Birthday', 'Business dinner', 'Anniversary', 'Just dining'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Birthday', 'next' => 4],
                            ['operator' => 'equals', 'value' => 'Business dinner', 'next' => 5],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Would you like us to prepare a birthday surprise?', 'options' => ['Yes, cake and candles', 'Yes, just a card', 'No thanks'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Do you need a private room or AV equipment?', 'is_required' => true, 'options' => ['Private room', 'AV equipment', 'Both', 'Neither']],
                    ['type' => 'radio', 'question_text' => 'Preferred time slot', 'is_required' => true, 'options' => ['Lunch (11:30am-2pm)', 'Early dinner (5pm-7pm)', 'Late dinner (7pm-9pm)']],
                    ['type' => 'radio', 'question_text' => 'Seating preference', 'options' => ['Indoor', 'Outdoor', 'Bar area', 'No preference']],
                    ['type' => 'checkbox', 'question_text' => 'Any dietary requirements?', 'options' => ['Vegetarian', 'Vegan', 'Gluten-free', 'Nut allergy', 'None']],
                    ['type' => 'text', 'question_text' => 'Additional requests or notes', 'settings' => ['placeholder' => 'High chair, parking, etc.']],
                ],
            ],

            // ── Marketing ────────────────────────────────────────

            // Q0 name → Q1 email → Q2 role? branch:
            //   Business owner → Q3 biz topics → always→Q5
            //   Developer → Q4 dev topics → always→Q5
            //   Marketer/Other → default Q5
            // Q5 frequency → Q6 discovery → Q7 referral program?
            //   Yes → Q8 referral email
            //   No → end
            [
                'slug' => 'newsletter-signup',
                'title' => 'Newsletter Signup',
                'description' => 'Grow your email list with a simple signup form.',
                'category' => 'marketing',
                'icon' => 'mail',
                'theme' => ['background_color' => '#7C3AED', 'question_color' => '#FFFFFF', 'answer_color' => '#DDD6FE', 'button_color' => '#FFFFFF', 'button_text_color' => '#7C3AED', 'font' => 'Inter', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'What is your first name?', 'is_required' => true, 'settings' => ['placeholder' => 'Jane']],
                    ['type' => 'email', 'question_text' => 'Your email address', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'radio', 'question_text' => 'What best describes your role?', 'is_required' => true, 'options' => ['Business owner', 'Developer', 'Marketer', 'Other'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Business owner', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'Developer', 'next' => 4],
                        ],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'Which business topics interest you?', 'is_required' => true, 'options' => ['Growth strategies', 'Case studies', 'Product updates', 'Industry trends'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'Which developer topics interest you?', 'is_required' => true, 'options' => ['API updates', 'Tutorials', 'Open source', 'Architecture deep dives']],
                    ['type' => 'radio', 'question_text' => 'How often would you like to hear from us?', 'is_required' => true, 'options' => ['Weekly', 'Bi-weekly', 'Monthly']],
                    ['type' => 'radio', 'question_text' => 'How did you discover us?', 'options' => ['Search engine', 'Social media', 'Friend or colleague', 'Blog post', 'Other']],
                    ['type' => 'radio', 'question_text' => 'Would you like to join our referral program?', 'options' => ['Yes, tell me more', 'No thanks'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'No thanks', 'next' => 9],
                        ],
                    ],
                    ['type' => 'email', 'question_text' => 'Email of someone you\'d like to refer', 'settings' => ['placeholder' => 'friend@example.com']],
                    ['type' => 'text', 'question_text' => 'Anything else you\'d like us to know?', 'settings' => ['placeholder' => 'Questions, suggestions...']],
                ],
            ],

            // ── Support ──────────────────────────────────────────

            // Q0 summary → Q1 severity? branch:
            //   Critical/Major → Q2 steps to reproduce → Q3 expected → Q4 actual → Q5 workaround? branch:
            //     Yes → Q6 describe workaround → always→Q8
            //     No → default Q7 blocking? → Q8
            //   Minor/Cosmetic → Q7... wait, let me restructure.
            //
            // Better:
            // Q0 summary → Q1 severity? branch:
            //   Critical → Q2 (blocking question) → always→Q4
            //   Major → Q3 (workaround?) → Q4
            //   Minor/Cosmetic → default Q4
            // Q4 steps to reproduce → Q5 expected → Q6 actual → Q7 browser → Q8 device → Q9 email
            [
                'slug' => 'bug-report',
                'title' => 'Bug Report',
                'description' => 'Let users report bugs and technical issues with clear details.',
                'category' => 'support',
                'icon' => 'bug',
                'theme' => ['background_color' => '#0B1220', 'question_color' => '#F8FAFC', 'answer_color' => '#60A5FA', 'button_color' => '#3B82F6', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'small'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'Summarize the issue in one sentence', 'is_required' => true, 'settings' => ['placeholder' => 'e.g. Checkout button doesn\'t respond on mobile']],
                    ['type' => 'radio', 'question_text' => 'How severe is the issue?', 'is_required' => true, 'options' => ['Critical — blocks all usage', 'Major — key feature broken', 'Minor — annoying but workable', 'Cosmetic'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Critical — blocks all usage', 'next' => 2],
                            ['operator' => 'equals', 'value' => 'Major — key feature broken', 'next' => 3],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Is this completely blocking your work?', 'is_required' => true, 'options' => ['Yes, I cannot continue at all', 'Partially, some things still work'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 4]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Have you found a workaround?', 'is_required' => true, 'options' => ['Yes', 'No']],
                    ['type' => 'text', 'question_text' => 'Steps to reproduce the bug', 'is_required' => true, 'settings' => ['placeholder' => '1. Go to... 2. Click... 3. See error']],
                    ['type' => 'text', 'question_text' => 'What did you expect to happen?', 'is_required' => true, 'settings' => ['placeholder' => 'Describe the expected behavior']],
                    ['type' => 'text', 'question_text' => 'What actually happened?', 'is_required' => true, 'settings' => ['placeholder' => 'Describe what went wrong']],
                    ['type' => 'radio', 'question_text' => 'Which browser are you using?', 'options' => ['Chrome', 'Firefox', 'Safari', 'Edge', 'Other']],
                    ['type' => 'radio', 'question_text' => 'Which device?', 'options' => ['Desktop', 'Tablet', 'Mobile'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Desktop', 'next' => 10],
                            ['operator' => 'equals', 'value' => 'Tablet', 'next' => 10],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Which mobile OS?', 'options' => ['iOS', 'Android', 'Other']],
                    ['type' => 'email', 'question_text' => 'Your email (so we can follow up)', 'settings' => ['placeholder' => 'you@example.com']],
                ],
            ],

            // Q0 name → Q1 email → Q2 help topic? branch:
            //   Account & billing → Q3 billing type → always→Q7
            //   Technical issue → Q4 area? → Q5 describe → always→Q7
            //   Feature question → Q6 which feature
            //   Other → default Q7
            // Q7 urgency? branch:
            //   High → Q8 phone number
            //   Low/Medium → Q9 preferred contact
            // Q8,Q9 → end
            [
                'slug' => 'support-request',
                'title' => 'Support Request',
                'description' => 'Collect customer support tickets with priority routing.',
                'category' => 'support',
                'icon' => 'lifebuoy',
                'theme' => ['background_color' => '#F0FDFA', 'question_color' => '#134E4A', 'answer_color' => '#0D9488', 'button_color' => '#14B8A6', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'Your name', 'is_required' => true, 'settings' => ['placeholder' => 'Jane Doe']],
                    ['type' => 'email', 'question_text' => 'Your email address', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'radio', 'question_text' => 'What do you need help with?', 'is_required' => true, 'options' => ['Account & billing', 'Technical issue', 'Feature question', 'Other'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Account & billing', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'Technical issue', 'next' => 4],
                            ['operator' => 'equals', 'value' => 'Feature question', 'next' => 6],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'What type of billing issue?', 'is_required' => true, 'options' => ['Charge I don\'t recognize', 'Cancel subscription', 'Upgrade or downgrade', 'Invoice needed'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 7]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Which area is affected?', 'is_required' => true, 'options' => ['Login/authentication', 'Dashboard', 'Forms', 'Integrations', 'Other']],
                    ['type' => 'text', 'question_text' => 'Describe the technical issue in detail', 'is_required' => true, 'settings' => ['placeholder' => 'What happened, error messages, steps to reproduce...'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 7]],
                    ],
                    ['type' => 'text', 'question_text' => 'Which feature do you have a question about?', 'is_required' => true, 'settings' => ['placeholder' => 'Feature name and your question...']],
                    ['type' => 'radio', 'question_text' => 'How urgent is this?', 'is_required' => true, 'options' => ['High — blocking my work', 'Medium — need help soon', 'Low — no rush'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'High — blocking my work', 'next' => 8],
                            ['operator' => 'equals', 'value' => 'Medium — need help soon', 'next' => 9],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'Phone number for urgent callback', 'is_required' => true, 'settings' => ['placeholder' => '+1 (555) 000-0000'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 10]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Preferred contact method', 'options' => ['Email reply', 'Live chat', 'Phone call']],
                    ['type' => 'text', 'question_text' => 'Anything else we should know?', 'settings' => ['placeholder' => 'Additional context...']],
                ],
            ],

            // ── Education ────────────────────────────────────────

            // Q0 course name → Q1 format? branch:
            //   Online → Q2 rate platform → always→Q5
            //   In-person → Q3 rate classroom → always→Q5
            //   Hybrid → Q4 hybrid balance
            // Q4 → Q5 rate instructor → Q6 clarity → Q7 workload? branch:
            //   Too heavy → Q8 what to reduce → always→Q10
            //   Too light → Q9 what to add
            //   About right → default Q10
            // Q10 additional feedback
            [
                'slug' => 'teacher-evaluation',
                'title' => 'Teacher / Instructor Evaluation',
                'description' => 'Collect student feedback on courses and instructors.',
                'category' => 'education',
                'icon' => 'academic',
                'theme' => ['background_color' => '#1E1B4B', 'question_color' => '#E0E7FF', 'answer_color' => '#818CF8', 'button_color' => '#6366F1', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'Course name or code', 'is_required' => true, 'settings' => ['placeholder' => 'e.g. CS 101']],
                    ['type' => 'radio', 'question_text' => 'What was the course format?', 'is_required' => true, 'options' => ['Online', 'In-person', 'Hybrid'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Online', 'next' => 2],
                            ['operator' => 'equals', 'value' => 'In-person', 'next' => 3],
                        ],
                    ],
                    ['type' => 'rating', 'question_text' => 'How would you rate the online platform and streaming quality?', 'is_required' => true, 'settings' => ['max' => 5],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'rating', 'question_text' => 'How would you rate the classroom and facilities?', 'is_required' => true, 'settings' => ['max' => 5],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Was the balance between online and in-person effective?', 'is_required' => true, 'options' => ['Very effective', 'Somewhat effective', 'Not effective']],
                    ['type' => 'rating', 'question_text' => 'How would you rate the instructor overall?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'rating', 'question_text' => 'How clear were the explanations?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'radio', 'question_text' => 'Was the course workload reasonable?', 'is_required' => true, 'options' => ['Too heavy', 'About right', 'Too light'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Too heavy', 'next' => 8],
                            ['operator' => 'equals', 'value' => 'Too light', 'next' => 9],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What assignments or topics would you reduce?', 'is_required' => true, 'settings' => ['placeholder' => 'Be specific...'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 10]],
                    ],
                    ['type' => 'text', 'question_text' => 'What topics or activities would you add?', 'is_required' => true, 'settings' => ['placeholder' => 'What was missing...']],
                    ['type' => 'text', 'question_text' => 'Any additional feedback for the instructor?', 'settings' => ['placeholder' => 'Your thoughts...']],
                ],
            ],

            // Q0 student name → Q1 parent email → Q2 grade? branch:
            //   Elementary → Q3 after-school care → always→Q6
            //   Middle school → Q4 electives → always→Q6
            //   High school → Q5 extracurriculars
            // Q5 → Q6 medical → Q7 transportation? branch:
            //   School bus → Q8 bus route → always→Q10
            //   Parent drop-off → default Q10
            //   Walk/bike → Q9 permission
            // Q9 → Q10 emergency contact → Q11 notes
            [
                'slug' => 'student-registration',
                'title' => 'Student Registration',
                'description' => 'Register new students with grade-level branching.',
                'category' => 'education',
                'icon' => 'book',
                'theme' => ['background_color' => '#F5F3FF', 'question_color' => '#1E1B4B', 'answer_color' => '#7C3AED', 'button_color' => '#6D28D9', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'Student\'s full name', 'is_required' => true, 'settings' => ['placeholder' => 'Alex Johnson']],
                    ['type' => 'email', 'question_text' => 'Parent/guardian email', 'is_required' => true, 'settings' => ['placeholder' => 'parent@example.com']],
                    ['type' => 'radio', 'question_text' => 'Grade level', 'is_required' => true, 'options' => ['Elementary (K-5)', 'Middle school (6-8)', 'High school (9-12)'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Elementary (K-5)', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'Middle school (6-8)', 'next' => 4],
                            ['operator' => 'equals', 'value' => 'High school (9-12)', 'next' => 5],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Does the student need after-school care?', 'is_required' => true, 'options' => ['Yes, every day', 'Yes, some days', 'No'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'Which electives is the student interested in?', 'options' => ['Art', 'Music', 'Computer science', 'Foreign language', 'Drama'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 6]],
                    ],
                    ['type' => 'checkbox', 'question_text' => 'Which extracurricular programs?', 'options' => ['Athletics', 'Music/Band', 'Drama', 'Debate', 'STEM club']],
                    ['type' => 'checkbox', 'question_text' => 'Any allergies or medical conditions?', 'options' => ['Food allergy', 'Asthma', 'ADHD', 'Diabetes', 'None']],
                    ['type' => 'radio', 'question_text' => 'How will the student get to school?', 'is_required' => true, 'options' => ['School bus', 'Parent drop-off', 'Walk or bike'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'School bus', 'next' => 8],
                            ['operator' => 'equals', 'value' => 'Walk or bike', 'next' => 9],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'Nearest bus stop or cross-street for route planning', 'is_required' => true, 'settings' => ['placeholder' => 'e.g. Elm St & 5th Ave'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 10]],
                    ],
                    ['type' => 'radio', 'question_text' => 'Do you give permission for the student to walk/bike home unsupervised?', 'is_required' => true, 'options' => ['Yes', 'No']],
                    ['type' => 'text', 'question_text' => 'Emergency contact name and phone number', 'is_required' => true, 'settings' => ['placeholder' => 'Name — (555) 000-0000']],
                    ['type' => 'text', 'question_text' => 'Anything else we should know?', 'settings' => ['placeholder' => 'Special accommodations, notes, etc.']],
                ],
            ],

            // ── Marketing (additional) ───────────────────────────

            // Q0 age → Q1 purchase frequency? branch:
            //   Weekly/Monthly → Q2 where buy → always→Q5
            //   A few times a year → Q3 what triggers → always→Q5
            //   Rarely or never → Q4 barriers
            // Q4 → Q5 factors → Q6 tried our brand? branch:
            //   Yes → Q7 comparison → always→Q9
            //   No → Q8 what would convince
            // Q8 → Q9 email
            [
                'slug' => 'market-research',
                'title' => 'Market Research Survey',
                'description' => 'Understand your target audience, preferences, and buying habits.',
                'category' => 'marketing',
                'icon' => 'chart',
                'theme' => ['background_color' => '#F8FAFC', 'question_color' => '#0F172A', 'answer_color' => '#0EA5E9', 'button_color' => '#0284C7', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'small'],
                'questions' => [
                    ['type' => 'radio', 'question_text' => 'What is your age range?', 'is_required' => true, 'options' => ['18-24', '25-34', '35-44', '45-54', '55+']],
                    ['type' => 'radio', 'question_text' => 'How often do you purchase products in this category?', 'is_required' => true, 'options' => ['Weekly', 'Monthly', 'A few times a year', 'Rarely or never'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Weekly', 'next' => 2],
                            ['operator' => 'equals', 'value' => 'Monthly', 'next' => 2],
                            ['operator' => 'equals', 'value' => 'A few times a year', 'next' => 3],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Where do you typically purchase?', 'is_required' => true, 'options' => ['Online marketplace', 'Brand website', 'Physical store', 'Social media shop'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'text', 'question_text' => 'What typically triggers a purchase for you?', 'is_required' => true, 'settings' => ['placeholder' => 'Sale, recommendation, need...'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'text', 'question_text' => 'What has prevented you from purchasing?', 'is_required' => true, 'settings' => ['placeholder' => 'Price, availability, awareness...']],
                    ['type' => 'checkbox', 'question_text' => 'Which factors influence your purchase decisions most?', 'is_required' => true, 'options' => ['Price', 'Quality', 'Brand reputation', 'Reviews', 'Convenience']],
                    ['type' => 'radio', 'question_text' => 'Have you tried our product or brand before?', 'is_required' => true, 'options' => ['Yes', 'No'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Yes', 'next' => 7],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'How did our product compare to alternatives?', 'is_required' => true, 'options' => ['Much better', 'Somewhat better', 'About the same', 'Worse'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 9]],
                    ],
                    ['type' => 'text', 'question_text' => 'What would convince you to try our product?', 'is_required' => true, 'settings' => ['placeholder' => 'Free trial, discount, recommendation...']],
                    ['type' => 'email', 'question_text' => 'Your email (to receive the results summary)', 'settings' => ['placeholder' => 'you@example.com']],
                ],
            ],

            // Q0 attendance type? branch:
            //   In person → Q1 rate venue → always→Q3
            //   Virtual → Q2 rate streaming
            //   Hybrid → default Q3 (both)
            // Q3 rate overall → Q4 rate speakers → Q5 event length → Q6 attend again? branch:
            //   Definitely/Probably → Q8 takeaway
            //   Not sure/No → Q7 what would change
            // Q7 → Q8 takeaway → Q9 suggestions
            [
                'slug' => 'post-event-feedback',
                'title' => 'Post-Event Feedback',
                'description' => 'Gather attendee feedback after an event, conference, or webinar.',
                'category' => 'feedback',
                'icon' => 'clipboard',
                'theme' => ['background_color' => '#FFFBEB', 'question_color' => '#1C1917', 'answer_color' => '#D97706', 'button_color' => '#F59E0B', 'button_text_color' => '#1C1917', 'font' => 'Inter', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'radio', 'question_text' => 'How did you attend the event?', 'is_required' => true, 'options' => ['In person', 'Virtual/online', 'Hybrid'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'In person', 'next' => 1],
                            ['operator' => 'equals', 'value' => 'Virtual/online', 'next' => 2],
                        ],
                    ],
                    ['type' => 'rating', 'question_text' => 'How would you rate the venue and logistics?', 'is_required' => true, 'settings' => ['max' => 5],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 3]],
                    ],
                    ['type' => 'rating', 'question_text' => 'How would you rate the online streaming quality?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'rating', 'question_text' => 'How would you rate the event overall?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'rating', 'question_text' => 'How would you rate the speakers/presenters?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'radio', 'question_text' => 'How was the event length?', 'is_required' => true, 'options' => ['Too short', 'Just right', 'Too long']],
                    ['type' => 'radio', 'question_text' => 'Would you attend a similar event in the future?', 'is_required' => true, 'options' => ['Definitely', 'Probably', 'Not sure', 'No'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Definitely', 'next' => 8],
                            ['operator' => 'equals', 'value' => 'Probably', 'next' => 8],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What would need to change for you to attend again?', 'is_required' => true, 'settings' => ['placeholder' => 'Topics, format, speakers, price...']],
                    ['type' => 'text', 'question_text' => 'What was the most valuable thing you took away?', 'settings' => ['placeholder' => 'Key takeaway...']],
                    ['type' => 'text', 'question_text' => 'Any suggestions for our next event?', 'settings' => ['placeholder' => 'We\'d love to hear your ideas']],
                ],
            ],

            // Q0 reason? branch:
            //   Too expensive → Q1 lower price? → always→Q5
            //   Missing features → Q2 which features → always→Q5
            //   Switched to competitor → Q3 which competitor → always→Q5
            //   No longer needed → Q4 why not needed
            //   Poor experience → default Q5
            // Q5 overall rating → Q6 come back? branch:
            //   Yes → Q8 email
            //   Maybe → Q7 what would bring you back
            //   No → Q7
            // Q7 → Q8 email
            [
                'slug' => 'cancellation-survey',
                'title' => 'Cancellation / Churn Survey',
                'description' => 'Understand why customers cancel with reason-based branching.',
                'category' => 'feedback',
                'icon' => 'xCircle',
                'theme' => ['background_color' => '#18181B', 'question_color' => '#FAFAFA', 'answer_color' => '#F87171', 'button_color' => '#EF4444', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'radio', 'question_text' => 'What is your primary reason for cancelling?', 'is_required' => true, 'options' => ['Too expensive', 'Missing features', 'Switched to competitor', 'No longer needed', 'Poor experience'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Too expensive', 'next' => 1],
                            ['operator' => 'equals', 'value' => 'Missing features', 'next' => 2],
                            ['operator' => 'equals', 'value' => 'Switched to competitor', 'next' => 3],
                            ['operator' => 'equals', 'value' => 'No longer needed', 'next' => 4],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Would a lower-priced plan have kept you?', 'is_required' => true, 'options' => ['Yes, definitely', 'Maybe', 'No, price wasn\'t the only issue'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'text', 'question_text' => 'Which features were you missing?', 'is_required' => true, 'settings' => ['placeholder' => 'Describe the features you needed...'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'text', 'question_text' => 'Which competitor did you switch to and why?', 'is_required' => true, 'settings' => ['placeholder' => 'Name and what made them a better fit'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 5]],
                    ],
                    ['type' => 'text', 'question_text' => 'What changed that you no longer need our product?', 'is_required' => true, 'settings' => ['placeholder' => 'Project ended, changed roles, etc.']],
                    ['type' => 'rating', 'question_text' => 'How would you rate your overall experience with us?', 'is_required' => true, 'settings' => ['max' => 5]],
                    ['type' => 'radio', 'question_text' => 'Would you consider coming back in the future?', 'is_required' => true, 'options' => ['Yes', 'Maybe', 'No'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Maybe', 'next' => 7],
                            ['operator' => 'equals', 'value' => 'No', 'next' => 7],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'What would bring you back?', 'is_required' => true, 'settings' => ['placeholder' => 'Lower price, new feature, better support...']],
                    ['type' => 'email', 'question_text' => 'Your email (optional, if you want us to reach out)', 'settings' => ['placeholder' => 'you@example.com']],
                ],
            ],

            // Q0 name → Q1 email → Q2 attending? branch:
            //   Joyfully accept → Q3 guests → Q4 dietary → Q5 ceremony only or reception too? branch:
            //     Ceremony only → Q8 message
            //     Both → Q6 song request → Q7 plus-one name
            //   Regretfully decline → Q8 message
            // Q7 → Q8 message
            [
                'slug' => 'wedding-rsvp',
                'title' => 'Wedding RSVP',
                'description' => 'Collect RSVPs for your wedding or celebration.',
                'category' => 'registration',
                'icon' => 'heart',
                'theme' => ['background_color' => '#FFF1F2', 'question_color' => '#1C1917', 'answer_color' => '#E11D48', 'button_color' => '#BE123C', 'button_text_color' => '#FFFFFF', 'font' => 'Georgia', 'border_radius' => 'large'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'Your full name', 'is_required' => true, 'settings' => ['placeholder' => 'As it appears on the invitation']],
                    ['type' => 'email', 'question_text' => 'Your email address', 'is_required' => true, 'settings' => ['placeholder' => 'you@example.com']],
                    ['type' => 'radio', 'question_text' => 'Will you be attending?', 'is_required' => true, 'options' => ['Joyfully accept', 'Regretfully decline'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Regretfully decline', 'next' => 8],
                        ],
                    ],
                    ['type' => 'number', 'question_text' => 'Number of guests (including yourself)', 'is_required' => true, 'settings' => ['placeholder' => '1']],
                    ['type' => 'checkbox', 'question_text' => 'Dietary requirements', 'options' => ['Vegetarian', 'Vegan', 'Gluten-free', 'Kosher', 'None']],
                    ['type' => 'radio', 'question_text' => 'Will you attend both the ceremony and the reception?', 'is_required' => true, 'options' => ['Both', 'Ceremony only'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Ceremony only', 'next' => 8],
                        ],
                    ],
                    ['type' => 'text', 'question_text' => 'Song request for the dance floor', 'settings' => ['placeholder' => 'Song title — Artist']],
                    ['type' => 'text', 'question_text' => 'Names of your plus-ones (if bringing guests)', 'settings' => ['placeholder' => 'Guest names...']],
                    ['type' => 'text', 'question_text' => 'Any message for the couple?', 'settings' => ['placeholder' => 'Your well-wishes...']],
                ],
            ],

            // ── Quiz ─────────────────────────────────────────────

            // Q0 name → Q1 email → Q2 company → Q3 team size → Q4 primary need? branch:
            //   Lead generation → Q5 current lead gen → always→Q7
            //   Customer feedback → Q6 current feedback method
            //   Internal surveys / Event management → default Q7
            // Q7 budget? branch:
            //   Under $50 → Q9 anything else (skip demo)
            //   $50-$200 → default Q8
            //   $200-$500 → Q8 demo?
            //   $500+ → Q8 demo?
            // Q8 demo? → Q9 anything else
            [
                'slug' => 'lead-generation-quiz',
                'title' => 'Lead Generation Quiz',
                'description' => 'Qualify leads with a short quiz about their needs and budget.',
                'category' => 'quiz',
                'icon' => 'zap',
                'theme' => ['background_color' => '#0F172A', 'question_color' => '#F1F5F9', 'answer_color' => '#38BDF8', 'button_color' => '#0EA5E9', 'button_text_color' => '#FFFFFF', 'font' => 'Inter', 'border_radius' => 'medium'],
                'questions' => [
                    ['type' => 'text', 'question_text' => 'What is your name?', 'is_required' => true, 'settings' => ['placeholder' => 'Jane Smith']],
                    ['type' => 'email', 'question_text' => 'Your work email', 'is_required' => true, 'settings' => ['placeholder' => 'you@company.com']],
                    ['type' => 'text', 'question_text' => 'What is your company name?', 'is_required' => true, 'settings' => ['placeholder' => 'Acme Inc.']],
                    ['type' => 'radio', 'question_text' => 'What is your team size?', 'is_required' => true, 'options' => ['Just me', '2-10', '11-50', '50+']],
                    ['type' => 'radio', 'question_text' => 'What is your primary need?', 'is_required' => true, 'options' => ['Lead generation', 'Customer feedback', 'Internal surveys', 'Event management'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => 'Lead generation', 'next' => 5],
                            ['operator' => 'equals', 'value' => 'Customer feedback', 'next' => 6],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'How are you currently generating leads?', 'is_required' => true, 'options' => ['Landing pages', 'Social media ads', 'Cold outreach', 'Not currently'],
                        'logic' => [['operator' => 'always', 'value' => '', 'next' => 7]],
                    ],
                    ['type' => 'radio', 'question_text' => 'How do you currently collect feedback?', 'is_required' => true, 'options' => ['Email surveys', 'In-app widgets', 'Phone calls', 'We don\'t yet']],
                    ['type' => 'radio', 'question_text' => 'What is your monthly budget for this?', 'is_required' => true, 'options' => ['Under $50', '$50-$200', '$200-$500', '$500+'],
                        'logic' => [
                            ['operator' => 'equals', 'value' => '$200-$500', 'next' => 8],
                            ['operator' => 'equals', 'value' => '$500+', 'next' => 8],
                        ],
                    ],
                    ['type' => 'radio', 'question_text' => 'Would you like a personalized demo?', 'options' => ['Yes, schedule a call', 'Send me a recording', 'Not right now']],
                    ['type' => 'text', 'question_text' => 'Anything else we should know?', 'settings' => ['placeholder' => 'Your goals, timeline, questions...']],
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        return collect(static::all())->firstWhere('slug', $slug);
    }

    public static function categories(): array
    {
        return [
            'feedback' => 'Feedback',
            'registration' => 'Registration',
            'hr' => 'HR',
            'order' => 'Orders',
            'marketing' => 'Marketing',
            'support' => 'Support',
            'education' => 'Education',
            'quiz' => 'Quiz',
        ];
    }
}
