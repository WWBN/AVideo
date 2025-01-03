
# FullCalendar Timeline Plugin

Display events on a horizontal time axis (without resources)

## Installation

Install the necessary packages:

```sh
npm install @fullcalendar/core @fullcalendar/timeline
```

## Usage

Instantiate a Calendar with the necessary plugin:

```js
import { Calendar } from '@fullcalendar/core'
import timelinePlugin from '@fullcalendar/timeline'

const calendarEl = document.getElementById('calendar')
const calendar = new Calendar(calendarEl, {
  plugins: [timelinePlugin],
  initialView: 'timelineWeek',
  events: [
    { title: 'Meeting', start: new Date() }
  ]
})

calendar.render()
```
