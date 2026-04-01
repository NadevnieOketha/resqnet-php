Project Name : ResQnet21 — Disaster Management \& Response Coordination Platform



Project Vision:ResQnet21 is a full-stack, role-based disaster management system designed to: Improve early warning systems Enable real-time disaster reporting Coordinate volunteers, NGOs, and government officials Ensure transparent donation and relief distribution Provide public awareness and preparedness tools



The system integrates real-time APIs, geospatial mapping, and multi-role workflows to deliver a centralized disaster response ecosystem.



System Architecture Overview



Core Architecture Style

* Role-Based Access Control (RBAC)
* Modular MVC Architecture (Feature-Based MVC)
* API-driven integrations
* External Integrations
* Open-Meteo API → Weather and rainfall forecasts
* OpenStreetMap API → Location and safe shelter visualization
* Notify.lk API → SMS alerts and warnings



User Roles and Responsibilities



General Public

* View forecasts and alerts
* Report disasters
* Donate items
* Register as volunteers
* Participate in community forum
* Request Donations



Volunteer

* Receive assigned disaster tasks automatically
* View assigned and nearby disasters
* Accept or decline assignments
* Submit real-time field updates



NGO

* Manage donations
* Track inventory
* Handle collection points



Grama Niladhari

* Request relief supplies
* Manage safe location occupancy



Disaster Management Center (DMC)

* System administrators
* Verify reports and volunteers
* Manage national disaster data
* Generate transparency reports



Public (Unauthenticated) Features

* Real-Time Weather Dashboard - 7-day rainfall forecast, Flood risk levels (Low, Moderate, High), Data fetched from Open-Meteo API
* Community Forum (Read-only) - View posts, updates, No login required for reading
* Quick Disaster Reporting - No login required, Inputs include name, contact, disaster type, date/time, location, and image upload, Risk of fake reports handled via DMC verification
* Anonymous Donations - Select collection point, date, and time slot, Choose items (Food, Medicine, Shelter), Optional notes, and condition confirmation



Role-Based Registration - Roles: General User, NGO, Volunteer

Authentication System - Login using email or username, Password reset via email, Role-based dashboard redirection



Authenticated Features 



General Public Features 

* Forecast Dashboard - Select river and gauge station, View rainfall chart and flood risk trends, Real-time API data
* SMS Alerts - Opt-in via profile, Triggered by river thresholds, Powered by Notify.lk
* Safe Location Map - OpenStreetMap integration, Shows nearby shelters and navigation support 
* Donations (Logged-in) - Auto-filled user data, Faster submission
* Disaster Reporting (Logged-in) - Pre-filled contact information, Faster reporting
* Volunteer Registration - Skill-based signup, Requires DMC approval
* Community Forum - Create posts and upload images, Moderated by DMC
* Profile Management - Edit personal information, Enable or disable SMS alerts, Change password, Phone verification required



Volunteer Features 

* View Assigned and Nearby Disasters - See automatically assigned tasks, View additional nearby disasters for voluntary participation
* Real-Time Field Updates : Submit situation details and images, Helps coordination with DMC



NGO Features

* Donation Management - Track pending, received, and cancelled donations, Mark items as received
* Inventory Management - Automatic stock tracking (More than 20 → In Stock, Less than 20 → Low, Zero → Out of Stock), Manual updates supported
* Collection Points Management - Add, edit, and delete locations, Store address and contact information



Grama Niladhari Features



* Request Supplies - Input category, headcount, and duration, System auto-calculates required items, Requests sent to NGO dashboard
* Manage Safe Locations - Update capacity and occupancy, Enables real-time shelter availability



Disaster Management Center (DMC) 

* 
* Manage Grama Niladhari Accounts - Add, edit, and deactivate officials
* River Basin Monitoring - Track water levels, Configure alert, warning, and danger thresholds, Automatically trigger SMS alerts
* Verify Disaster Reports - Approve or reject reports, Prevent misinformation
* Reporting Dashboard - Track donations and distribution, Generate transparency reports
* Verify Volunteers - Approve or reject applications
* Volunteer assignment management - 
* Manage Safe Locations (Global) - Add, edit, and remove shelters, Maintain national database
* Forum Moderation - Approve, edit, or delete posts, Prevent misinformation
* Confirm NGO Deliveries - Validate received aid, Update system inventory



Automated Volunteer Task Assignment System Module - The system includes an automated mechanism to assign disaster response tasks to volunteers based on real-time data, ensuring rapid and efficient deployment of human resources.



Assignment Triggers

Tasks are automatically generated when:

* A disaster report is verified by DMC
* A disaster status escalates (e.g., flood level increases)
* A Grama Niladhari submits a high-priority supply request



Assignment Logic

The system assigns volunteers based on:

* Location proximity to disaster
* Volunteer skill set (e.g., medical, rescue, logistics)
* Availability status
* Workload balancing (avoid over-assigning individuals)



Volunteer Interface

* View assigned tasks
* Accept or decline assignments
* Submit updates and completion status



DMC has full oversight of the assignment system:



* View all assigned, pending, and completed tasks
* Manually override assignments if needed
* Reassign tasks if volunteers decline or fail to respond
* Monitor response times and effectiveness
* Ensure equitable distribution of workload



Each task follows a lifecycle: Pending → Assigned → Accepted → In Progress → Completed → Verified





Core Workflows



Disaster Flow

1. Public reports disaster
2. DMC verifies report
3. Volunteers are notified
4. Volunteers submit field updates



Donation Flow

1. Donor submits donation
2. NGO collects and marks as received
3. Inventory is updated
4. DMC tracks distribution



Relief Request Flow

1. Grama Niladhari submits request
2. NGO fulfills request
3. DMC monitors delivery



Alert System Flow

1. River threshold exceeded
2. System triggers SMS alerts
3. Users receive warnings



Automated Volunteer task assignment System Flow 

1. Disaster is verified by DMC
2. System identifies required task types
3. Matching volunteers are selected automatically
4. Task notifications are sent (dashboard and optionally SMS)
5. Volunteers can accept or decline assignments
6. Accepted tasks are marked as active





Key Challenges and Considerations

* Handling fake disaster reports
* Maintaining real-time synchronization
* SMS delivery reliability and cost
* Accurate geolocation mapping
* Inventory consistency across NGOs
* System scalability during large-scale disasters

