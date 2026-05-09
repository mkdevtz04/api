# Dating App - Flutter Mobile & Laravel API Planning

## 1. PROJECT OVERVIEW
- Project Name: Dating App
- Tech Stack: Flutter (mobile) + Laravel (backend)
- Target Platforms: iOS & Android
- Key Features: User authentication (email/OTP), swiping system, messaging, user profiles

## 2. FLUTTER APP ARCHITECTURE

### Navigation Structure
- AuthFlow (Login, Register, OTP verification)
- MainFlow (Home/Discover, Profile, Matches, Messages, Account)

### Core Screens
- **Auth Screens:**
  - SplashScreen
  - LoginScreen
  - RegisterScreen
  - OtpVerificationScreen
  
- **Main Screens:**
  - DiscoverScreen (Swiping cards)
  - ProfileScreen (User profile)
  - MatchesScreen (Matched users)
  - ChatScreen (Messaging)
  - AccountSettings

### State Management
- Provider / Riverpod / GetX (choose one)
- Authentication state provider
- User profile provider
- Matches & swipes provider
- Chat/messages provider

## 3. API INTEGRATION POINTS
- Auth endpoints (register, login, OTP)
- User profile endpoints (get, update)
- Swipe endpoints (create, retrieve)
- Matches endpoints (get matches)
- Messages endpoints (send, receive)

## 4. DATA MODELS
- User model (from API)
- Swipe model
- Match model
- Message model
- UserInterest model
- UserPhoto model

## 5. KEY FEATURES BREAKDOWN
1. Authentication
2. User Discovery/Swiping
3. Matching System
4. Real-time Messaging
5. User Profiles & Photos

## 6. DEPENDENCIES & PACKAGES
- http / dio (API calls)
- provider / riverpod (state management)
- intl (localization & dates)
- cached_network_image (images)
- websockets (real-time chat)