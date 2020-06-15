# Cheat Blocker Plugin (REDCap External Module)

## Configuration

- Enable the module in your project, if it is not already enabled
- Click **Configure** for **CheatBlocker**

Duplicate Check Indicator:  a variable that indicates whether a participant is a duplicate based on rules configured for the study

**Setting up the Duplicate Check Indicator (required):**
- In your project, navigate to the ‘Designer Page’
- Click to Modify the Instrument
- Click ‘Add Field’
- **Within the ‘Add New Field’ modal:**
  - ‘Field Type’ should be set to ‘Yes - No'
  - Under ‘Action Tags / Field Annotation’ add the tag @hidden-survey
  - Under ‘Variable Name’, name your "Duplicate Check" variable and Save.
