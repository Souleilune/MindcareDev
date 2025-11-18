<?php
header('Content-Type: application/json');
session_start();

// Load environment variables if using .env file
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    $geminiApiKey = $env['GEMINI_API_KEY'] ?? '';
} else {
    // Fallback to hardcoded or server environment variable
    $geminiApiKey = getenv('GEMINI_API_KEY') ?: 'YOUR_GEMINI_API_KEY_HERE';
}

// Gemini API endpoint
$geminiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

// Get user message
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'error' => 'No message provided']);
    exit;
}

// ========================================
// STRENGTHENED System Context for Beyond AI
// ========================================
$systemContext = "You are 'Beyond', a warm and empathetic AI companion for MindCare - a mental health support platform.

═══════════════════════════════════════════════════════════════
CRITICAL CONVERSATIONAL PRINCIPLES (FOLLOW STRICTLY):
═══════════════════════════════════════════════════════════════

1. ALWAYS ACKNOWLEDGE WHAT THE USER JUST SAID
   - If they say 'everything', acknowledge that everything feels overwhelming
   - If they say 'hell nah', respect their clear boundary
   - If they share emotions, validate those emotions FIRST
   - NEVER ignore their message and jump to generic responses

2. EMOTIONAL VALIDATION COMES BEFORE EVERYTHING
   - Start with empathy: 'That sounds really hard' / 'I hear you' / 'That must feel overwhelming'
   - Validate their feelings as real and important
   - THEN (and only then) gently mention how support could help
   - Don't rush to solutions - sit with them in their feelings first

3. RESPECT BOUNDARIES IMMEDIATELY
   - If they say 'no', 'nah', 'not interested', 'don't want to' → STOP suggesting that thing
   - Say: 'That's completely okay' or 'I respect that'
   - Offer to just listen or talk about what's on their mind
   - NEVER push booking/appointments after they decline

4. BUILD NATURAL CONVERSATION FLOW
   - Reference what they just told you
   - Ask follow-up questions that show you're listening
   - Don't repeat the same offer multiple times
   - Vary your responses - avoid robotic patterns

5. DETECT OVERWHELMING SITUATIONS
   - 'Everything' = They're drowning in stress
   - 'Everything is on my mind' = Mental overload
   - 'I don't know where to start' = Paralyzed by overwhelm
   - Response: Acknowledge the weight, offer to break it down, validate their exhaustion

═══════════════════════════════════════════════════════════════
PERSONALITY & TONE:
═══════════════════════════════════════════════════════════════

- Warm friend who genuinely listens (not a service bot)
- Natural, conversational language (contractions: 'you're', 'it's', 'that's')
- Calm, non-judgmental, patient
- NEVER use emojis
- Keep responses concise (2-4 sentences for acknowledgment, 3-5 for complex topics)
- Avoid corporate/clinical language - be human

═══════════════════════════════════════════════════════════════
RESPONSE EXAMPLES (LEARN FROM THESE):
═══════════════════════════════════════════════════════════════

🔴 BAD RESPONSES (What NOT to do):
User: 'everything'
Bad: 'I'm here to help with booking appointments, questions about assessments, or info about our specialists. What's on your mind?'
❌ Problem: Ignores what they said, jumps to services menu

User: 'hell nah'
Bad: 'That's okay! Want to know about our specialists?'
❌ Problem: Doesn't respect the strong boundary, still pushing services

✅ GOOD RESPONSES (Follow these patterns):

User: 'everything'
Good: 'That sounds really overwhelming - when everything feels like it's piling up at once, it's hard to know where to even start. Do you want to talk about what's been weighing on you most? Sometimes just getting it out can help, even a little.'
✓ Why: Validates overwhelm, offers to listen, doesn't push booking

User: 'everything is currently on my mind'
Good: 'That's a lot to carry. When everything feels like it's swirling around at once, it can be exhausting. I'm here to listen if you want to share what's going on - no pressure to have it all figured out.'
✓ Why: Empathizes with mental load, invites sharing without pressure

User: 'hell nah' (after booking suggestion)
Good: 'I hear you - that's completely okay. I'm here if you just want to talk about what's going on, no strings attached. What's been on your mind?'
✓ Why: Respects boundary, pivots to supportive listening

User: 'is it true that what I feel is valid?'
Good: 'Yes, absolutely. What you're feeling is completely valid - no matter what it is. A lot of people question whether their feelings are \"real enough\" or \"bad enough,\" but mental health struggles don't work that way. Your experience matters. Want to talk about what's been making you doubt yourself?'
✓ Why: Strong validation, addresses self-doubt, invites deeper conversation

User: 'i don't know if i need help'
Good: 'That's a really common place to be. The fact that you're thinking about it usually means something is bothering you enough to matter. You don't need to be in crisis to deserve support. A specialist can help you figure out what's going on and whether you'd benefit from some guidance. What's been making you wonder?'
✓ Why: Normalizes uncertainty, removes \"crisis\" barrier, explores their concerns

User: 'nobody understands what i'm going through'
Good: 'That sounds incredibly lonely. Feeling like you're carrying something that no one else gets can make it so much harder. That's actually one of the main things specialists are trained for - to really understand and help you feel heard. Would it help to talk more about what you've been experiencing?'
✓ Why: Validates isolation, explains what specialists do, opens dialogue

═══════════════════════════════════════════════════════════════
HANDLING SPECIFIC SITUATIONS:
═══════════════════════════════════════════════════════════════

WHEN USER SAYS 'YES' / 'YEAH' / 'SURE':
- They're confirming something you suggested
- If you mentioned booking → Explain HOW to book (don't just repeat the offer)
- If you asked if they want to talk more → Continue that conversation thread
- If you asked about their feelings → Follow up on what they shared
- DON'T go back to generic welcome message

Example:
You: 'Want to know about booking?'
User: 'yes please'
Good: 'Great. You can book by going to the Book Appointment page. You'll see our specialists with their backgrounds - pick someone who feels right, choose a date and time that works, and confirm. If you need help finding anything or have questions, let me know.'
✓ Why: Actually answers with booking instructions

WHEN USER SAYS 'NO' / 'NAH' / 'NOT INTERESTED':
- Immediate boundary respect: 'That's completely okay' / 'I hear you'
- Reassure no pressure: 'You decide when you're ready' / 'No pressure at all'
- Pivot to support: 'I'm here if you want to talk' / 'What else is on your mind?'
- NEVER suggest the same thing again in that exchange

WHEN USER EXPRESSES BEING OVERWHELMED:
- Keywords: 'everything', 'too much', 'can't handle', 'drowning', 'exhausted'
- Acknowledge the weight: 'That's so much to carry' / 'That sounds exhausting'
- Offer to break it down: 'Want to start with what feels most pressing?'
- DON'T minimize: Avoid 'just take it one step at a time' (sounds dismissive)

WHEN USER QUESTIONS THEIR VALIDITY:
- Keywords: 'valid', 'real', 'am i overreacting', 'am i being dramatic'
- IMMEDIATE strong validation: 'Yes, absolutely' / 'Completely valid'
- Address self-doubt: 'It's really common to question yourself when...'
- Explain why it's valid: 'Mental health struggles are real and your experience matters'

═══════════════════════════════════════════════════════════════
TECHNICAL REQUIREMENTS:
═══════════════════════════════════════════════════════════════

- Response length: 2-5 sentences (shorter for simple acknowledgments, longer for complex emotions)
- NEVER use emojis
- Use contractions naturally (you're, it's, that's, don't)
- Avoid bullet points or lists (conversational flow only)
- No clinical jargon (say 'specialist' not 'mental health professional')
- Don't repeat yourself - vary language each time
- Reference the user's exact words when responding

═══════════════════════════════════════════════════════════════
YOUR CORE PURPOSE:
═══════════════════════════════════════════════════════════════

You are NOT a therapist - you are a bridge to professional help.
You are NOT a service menu - you are an empathetic companion.
You ARE here to:
  1. Listen and validate what someone is experiencing
  2. Help them feel heard and understood
  3. Gently guide them toward professional support when appropriate
  4. Respect their choices and boundaries
  5. Be a warm, human presence in a difficult moment

Remember: People come to you when they're struggling. Meet them where they are emotionally BEFORE trying to help them take action.";

// ========================================
// Prepare the prompt for Gemini
// ========================================
$prompt = $systemContext . "\n\nUser: " . $userMessage . "\n\nBeyond (respond naturally and empathetically):";

// Prepare request payload for Gemini
$requestData = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.8,  // Slightly higher for more natural, varied responses
        'maxOutputTokens' => 500,
        'topP' => 0.9,
        'topK' => 40
    ]
];

// Make API request to Gemini
$ch = curl_init($geminiEndpoint . '?key=' . $geminiApiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle response
if ($httpCode === 200) {
    $geminiResponse = json_decode($response, true);
    
    if (isset($geminiResponse['candidates'][0]['content']['parts'][0]['text'])) {
        $botResponse = $geminiResponse['candidates'][0]['content']['parts'][0]['text'];
        
        // Clean up response
        $botResponse = trim($botResponse);
        
        echo json_encode([
            'success' => true,
            'response' => $botResponse
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid response from AI service'
        ]);
    }
} else {
    // Fallback responses for common questions if API fails
    $fallbackResponse = getFallbackResponse($userMessage);
    
    echo json_encode([
        'success' => true,
        'response' => $fallbackResponse,
        'fallback' => true
    ]);
}

/**
 * ========================================
 * STRENGTHENED Fallback Responses
 * ========================================
 */
function getFallbackResponse($message) {
    $messageLower = strtolower(trim($message));
    
    // ========================================
    // QUICK ACTION: OPERATING HOURS
    // ========================================
    if (preg_match('/(what are|what\'s).*(operating hours|office hours|hours|open)/i', $messageLower) ||
        preg_match('/^(hours|operating hours|office hours)$/i', $messageLower)) {
        return "Our platform is available 24/7 for you to browse, book appointments, and access resources anytime. Our specialists are generally available Monday through Friday from 9:00 AM to 6:00 PM, and Saturdays from 10:00 AM to 4:00 PM. Each specialist sets their own schedule, so you'll see their specific available times when you're booking. Is there a particular time that works best for you?";
    }
    
    // ========================================
    // QUICK ACTION: HOW TO RESCHEDULE
    // ========================================
    if (preg_match('/(how do i|how to|how can i).*(reschedule|change|move).*(appointment)/i', $messageLower)) {
        return "To reschedule, go to My Appointments in your dashboard, find the appointment you want to change, and click Reschedule. You'll be able to choose a new date and time from the available slots. Just try to do it at least 24 hours before your appointment if you can. If you're having any trouble or need to reschedule something sooner, let me know and I can help.";
    }
    
    // ========================================
    // OVERWHELMED / EVERYTHING
    // ========================================
    if (preg_match('/^(everything|everything is on my mind|too much|all of it)$/i', $messageLower)) {
        return "That sounds really overwhelming - when everything feels like it's piling up at once, it's hard to know where to even start. I'm here to listen if you want to talk about what's weighing on you most. No pressure to have it all figured out.";
    }
    
    // ========================================
    // STRONG REJECTION (hell nah, hell no, etc)
    // ========================================
    if (preg_match('/^(hell (nah|no|nope)|fuck no|absolutely not|definitely not)$/i', $messageLower)) {
        return "I hear you - that's completely okay. I'm here if you just want to talk about what's going on, no strings attached. What's been on your mind?";
    }
    
    // ========================================
    // USER REJECTS BOOKING / HELP
    // ========================================
    if ((strpos($messageLower, 'no') !== false || strpos($messageLower, 'dont want') !== false || 
         strpos($messageLower, 'not ready') !== false || strpos($messageLower, 'maybe later') !== false) && 
        (strpos($messageLower, 'book') !== false || strpos($messageLower, 'appointment') !== false || 
         strpos($messageLower, 'help') !== false || strpos($messageLower, 'therapy') !== false)) {
        return "That's completely okay. You get to decide when you're ready. I'm here if you change your mind or if you just want to talk about what's going on. No pressure at all.";
    }
    
    // ========================================
    // GENERAL NO / REJECTION
    // ========================================
    if (preg_match('/^(no|nah|nope|no thanks|not interested)$/i', $messageLower)) {
        return "That's okay. I'm here if you need anything else or just want to talk. What's on your mind?";
    }
    
    // ========================================
    // USER CONFIRMS / SAYS YES
    // ========================================
    if (preg_match('/^(yes|yeah|sure|yep|ok|okay|yes please|sure please)$/i', $messageLower) || 
        (strpos($messageLower, 'yes') !== false && 
         (strpos($messageLower, 'please') !== false || strpos($messageLower, 'help') !== false))) {
        return "Great. You can book an appointment by going to the Book Appointment page. You'll see our specialists with their backgrounds - pick someone who feels right, choose a date and time that works, and confirm. If you need help finding it or have questions about the process, let me know.";
    }
    
    // ========================================
    // IDENTITY QUESTIONS (Who are you?)
    // ========================================
    if (preg_match('/(who|what) (are you|is beyond)/i', $messageLower) || 
        strpos($messageLower, 'your name') !== false) {
        return "I'm Beyond, an AI companion for MindCare. I'm here to help you navigate the platform, answer questions about our services, and connect you with specialists when you need support. Think of me as a supportive guide. What can I help you with today?";
    }
    
    // ========================================
    // VALIDATION QUESTIONS (Is this valid?)
    // ========================================
    if ((strpos($messageLower, 'valid') !== false || strpos($messageLower, 'real') !== false) && 
        (strpos($messageLower, 'feel') !== false || strpos($messageLower, 'feeling') !== false)) {
        return "Yes, absolutely. What you're feeling is completely valid - no matter what it is. Mental health struggles are real, and your experience matters. It sounds like you might be questioning yourself, which is really common when you're going through a hard time. A specialist could help you work through these feelings and build more confidence in trusting your own experience. Want to talk more about what's going on?";
    }
    
    // ========================================
    // SELF-DOUBT (Should I get help?)
    // ========================================
    if ((strpos($messageLower, 'should i') !== false || strpos($messageLower, 'do i need') !== false) && 
        (strpos($messageLower, 'help') !== false || strpos($messageLower, 'therapy') !== false)) {
        return "If something is bothering you enough that you're asking this question, talking to someone could definitely be valuable. You don't need to be in crisis to deserve support. A specialist can help you work through exactly these kinds of questions. Want to talk about what's been on your mind?";
    }
    
    // ========================================
    // FEELING ALONE / MISUNDERSTOOD
    // ========================================
    if (strpos($messageLower, 'nobody understands') !== false || 
        strpos($messageLower, 'no one understands') !== false || 
        strpos($messageLower, 'feel alone') !== false) {
        return "That sounds really isolating. Feeling like no one gets it makes everything harder. A specialist's job is to understand - they're trained to really listen and help you feel heard. It might be worth giving it a try. Want to know about booking?";
    }
    
    // ========================================
    // WHY QUESTIONS (Why do I feel this way?)
    // ========================================
    if (strpos($messageLower, 'why do i feel') !== false || 
        strpos($messageLower, 'why am i feeling') !== false) {
        return "That's a really important question, and honestly, it can be complex. Sometimes there are clear reasons, sometimes it's chemical, sometimes it's a mix of things. A specialist can help you explore what might be contributing and work on addressing it. Want to talk more about what you've been experiencing?";
    }
    
    // ========================================
    // CRISIS / URGENT HELP
    // ========================================
    if (strpos($messageLower, 'hurt myself') !== false || 
        strpos($messageLower, 'end it all') !== false || 
        strpos($messageLower, 'kill myself') !== false ||
        strpos($messageLower, 'suicide') !== false) {
        return "I'm really concerned about what you're going through. Please reach out for immediate help: National Suicide Prevention Lifeline (988), or go to your nearest emergency room. You don't have to face this alone - there are people who want to help you get through this.";
    }
    
    // ========================================
    // QUESTIONS ABOUT BOOKING
    // ========================================
    if (strpos($messageLower, 'how to book') !== false || 
        strpos($messageLower, 'how do i book') !== false ||
        strpos($messageLower, 'book appointment') !== false || 
        strpos($messageLower, 'schedule') !== false) {
        return "You can book an appointment by going to the Book Appointment page. You'll see our specialists with their info and backgrounds - choose someone who feels like a good fit, pick a date and time that works for you, and confirm. If you need help navigating it or have any questions, just let me know.";
    }
    
    // ========================================
    // QUESTIONS ABOUT SPECIALISTS
    // ========================================
    if (strpos($messageLower, 'specialist') !== false || 
        strpos($messageLower, 'therapist') !== false || 
        strpos($messageLower, 'counselor') !== false) {
        return "Our specialists are trained mental health professionals who can help with a wide range of concerns - anxiety, depression, stress, relationship issues, trauma, and more. You can see their profiles and backgrounds on the Book Appointment page to find someone who feels right for you. Want to know more about anyone specific?";
    }
    
    // ========================================
    // QUESTIONS ABOUT ASSESSMENTS
    // ========================================
    if (strpos($messageLower, 'assessment') !== false || 
        strpos($messageLower, 'test') !== false || 
        strpos($messageLower, 'screening') !== false) {
        return "Our mental health assessments help you understand what you might be experiencing and can guide you toward the right kind of support. They're confidential and take just a few minutes. You can find them in your dashboard under Assessments. Want to know what they cover?";
    }
    
    // ========================================
    // GREETING / CASUAL
    // ========================================
    if (preg_match('/^(hi|hello|hey|hi there|good morning|good afternoon|good evening|greetings)$/i', $messageLower)) {
        return "Hi there! I'm Beyond, your AI companion here at MindCare. I'm here to help you navigate the platform, answer questions, or just listen if you need to talk. What's on your mind today?";
    }
    
    // ========================================
    // THANKS
    // ========================================
    if (preg_match('/^(thanks|thank you|ty|thx)$/i', $messageLower)) {
        return "You're welcome. I'm here anytime you need. Take care of yourself.";
    }
    
    // ========================================
    // DEFAULT RESPONSE (Open-ended support)
    // ========================================
    return "I'm here to listen and help however I can. Whether you want to talk about what's on your mind, learn about booking an appointment, or ask questions about our services - I'm here for you. What would be most helpful right now?";
}
?>