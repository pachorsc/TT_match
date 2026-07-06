# UI_GUIDELINES.md — User Interface Guidelines

## Design Philosophy

Desktop-first. Premium. Minimal. Modern sports analytics aesthetic.

## Layout Principles

- Desktop-first responsive design
- Large spacing throughout the interface
- Perfect alignment and grid consistency
- High readability at all screen sizes
- Minimal interface with maximum information density

## Color System

### Dark Theme

- Background: Deep dark tones (near-black or very dark gray)
- Cards: Slightly elevated dark surfaces with subtle contrast
- Text: High-contrast white and light gray hierarchy
- Borders: Thin, subtle borders (1px, low opacity)
- Accents: Used sparingly for interactive elements and data highlights

### Badge Colors

| State | Color |
|---|---|
| Win | Green |
| Loss | Red |
| Neutral | Gray |

## Card Design

- Border radius: 16–20px
- Soft shadows with low opacity
- Thin borders (1px) with subtle color
- Subtle glassmorphism effects where appropriate
- Large internal padding
- Consistent spacing between card elements

## Typography

- Excellent typography is mandatory
- Clear visual hierarchy: headings, subheadings, body text, labels
- Readable font sizes for desktop viewing
- Proper line heights and letter spacing
- Bold emphasis on key data points (rankings, scores, ratings)

## Components

### Header Section

- Player A vs Player B display
- Country flags displayed prominently
- World ranking and rating points clearly visible
- Dominant hand indicator
- Match title, tournament, date, and time centered

### Player Cards

- Two identical cards, one per player
- Clean layout with clear data hierarchy
- Name, country, flag, ranking, rating, hand, age, height, playing style
- Consistent card sizing and spacing

### Match Tables

- Two tables displayed side by side
- Clear column headers
- Rows with alternating subtle backgrounds (optional)
- Green badges for wins, red badges for losses
- Responsive behavior on smaller screens

### Head to Head Section

- Total matches count
- Win distribution by player
- Chronological match history
- Clean table format
- Graceful fallback message when no data exists

### Video Cards

- Thumbnail image from YouTube with play overlay
- Video title truncated with line clamping
- Publication date clearly shown
- Consistent card sizing in a responsive grid
- Graceful fallback when no videos are available (empty state with icon)
- Loading state with spinner while fetching from YouTube API
- Error state with retry messaging if API call fails

## Visual Elements to NEVER Include

- Player photos or avatars
- Betting odds or gambling references
- AI predictions or confidence percentages
- Win probabilities or statistical models
- Confidence bars or progress indicators
- Heat maps or spatial visualizations
- Decorative graphics or illustrations
- Unnecessary charts or graphs
- Animations that distract from data

## Spacing System

- Use consistent spacing scale throughout
- Generous margins between sections
- Adequate padding inside cards and containers
- Consistent gaps between grid items
- Vertical rhythm maintained across all sections

## Responsive Behavior

- Desktop-first: optimize for large screens
- Graceful degradation for smaller screens
- Tables should remain readable on tablet
- Cards should stack vertically on smaller viewports
- Maintain visual hierarchy at all breakpoints

## Inspiration References

| Source | What to Learn |
|---|---|
| Apple Sports | Clean data presentation, typography |
| SofaScore | Match data density, table design |
| ATP Tour | Player card layout, sports aesthetic |
| Flashscore | Live data presentation, dark theme |
| TradingView | Data visualization, dark theme execution |
