/*
 * Created on 2004-okt-05
 */
package se.optime.repos.calendar;

import java.io.BufferedReader;
import java.io.StringReader;

import org.jical.iCalendar;
import org.jical.iCalendarParser;

import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class IcalParserTest extends TestCase {

    public void testParseVcal() {
        BufferedReader input = new BufferedReader(new StringReader(VCAL_FILE));
        iCalendarParser parser = new iCalendarParser();
        iCalendar vcal = parser.iCalendarParser(input);
        System.out.println("[test-debug] " + vcal.getProdId());
    }
    
    public void testParseIcs() {
        
    }
    
    public static final String VCAL_FILE =
        "BEGIN:VCALENDAR\n"+
        "VERSION\n"+
        " :2.0\n"+
        "PRODID\n"+
        " :-//Mozilla.org/NONSGML Mozilla Calendar V1.0//EN\n"+
        "METHOD\n"+
        " :PUBLISH\n"+
        "BEGIN:VEVENT\n"+
        "UID\n"+
        " :22b50340-16aa-11d9-abc8-9456f9b7f112\n"+
        "SUMMARY\n"+
        " :Onsdag kl 12\n"+
        "LOCATION\n"+
        " :d√§r\n"+
        "STATUS\n"+
        " :TENTATIVE\n"+
        "CLASS\n"+
        " :PRIVATE\n"+
        "X-MOZILLA-RECUR-DEFAULT-INTERVAL\n"+
        " :0\n"+
        "DTSTART\n"+
        " :20041005T100000Z\n"+
        "DTEND\n"+
        " :20041005T120000Z\n"+
        "DTSTAMP\n"+
        " :20041005T083943Z\n"+
        "END:VEVENT\n"+
        "END:VCALENDAR\n";
    
    public static final String ICS_FILE =
        "VERSION\n"+
        " :2.0\n"+
        "PRODID\n"+
        " :-//Mozilla.org/NONSGML Mozilla Calendar V1.0//EN\n"+
        "METHOD\n"+
        " :PUBLISH\n"+
        "BEGIN:VEVENT\n"+
        "UID\n"+
        " :3def2df0-f1e9-11d8-b8c3-ba2126887bdd\n"+
        "SUMMARY\n"+
        " :dhgj\n"+
        "LOCATION\n"+
        " :kjhhkhj\n"+
        "STATUS\n"+
        " :TENTATIVE\n"+
        "CLASS\n"+
        " :PUBLIC\n"+
        "X-MOZILLA-RECUR-DEFAULT-INTERVAL\n"+
        " :0\n"+
        "DTSTART\n"+
        " :20040819T130000Z\n"+
        "DTEND\n"+
        " :20040819T140000Z\n"+
        "DTSTAMP\n"+
        " :20040819T140808Z\n"+
        "END:VEVENT\n"+
        "END:VCALENDAR\n"+
        "BEGIN:VCALENDAR\n"+
        "VERSION\n"+
        " :2.0\n"+
        "PRODID\n"+
        " :-//Mozilla.org/NONSGML Mozilla Calendar V1.0//EN\n"+
        "METHOD\n"+
        " :PUBLISH\n"+
        "BEGIN:VEVENT\n"+
        "UID\n"+
        " :70d36d10-f1e8-11d8-9c4c-db9b22e6a37d\n"+
        "SUMMARY\n"+
        " :ribjonok\n"+
        "DESCRIPTION\n"+
        " :sd\n"+
        "LOCATION\n"+
        " :hoooe\n"+
        "URL\n"+
        " :http://www.coo.se/show?id=1050\n"+
        "STATUS\n"+
        " :TENTATIVE\n"+
        "CLASS\n"+
        " :PUBLIC\n"+
        "X-MOZILLA-RECUR-DEFAULT-INTERVAL\n"+
        " :0\n"+
        "DTSTART\n"+
        " :20040819T165000Z\n"+
        "DTEND\n"+
        " :20040819T184500Z\n"+
        "DTSTAMP\n"+
        " :20040819T140129Z\n"+
        "END:VEVENT\n"+
        "END:VCALENDAR\n"+
        "BEGIN:VCALENDAR\n"+
        "VERSION\n"+
        " :2.0\n"+
        "PRODID\n"+
        " :-//Mozilla.org/NONSGML Mozilla Calendar V1.0//EN\n"+
        "METHOD\n"+
        " :PUBLISH\n"+
        "BEGIN:VEVENT\n"+
        "UID\n"+
        " :70d36d10-f1e8-11d8-9c4c-db9b22e6a37d\n"+
        "SUMMARY\n"+
        " :ribjonok\n"+
        "DESCRIPTION\n"+
        " :sd\n"+
        "LOCATION\n"+
        " :hoooe\n"+
        "URL\n"+
        " :http://www.coo.se/show?id=1050\n"+
        "STATUS\n"+
        " :TENTATIVE\n"+
        "CLASS\n"+
        " :PUBLIC\n"+
        "X-MOZILLA-RECUR-DEFAULT-INTERVAL\n"+
        " :0\n"+
        "DTSTART\n"+
        " :20040819T185000Z\n"+
        "DTEND\n"+
        " :20040819T204500Z\n"+
        "DTSTAMP\n"+
        " :20040819T140129Z\n"+
        "END:VEVENT\n"+
        "END:VCALENDAR\n"+
        "BEGIN:VCALENDAR\n"+
        "VERSION\n"+
        " :2.0\n"+
        "PRODID\n"+
        " :-//Mozilla.org/NONSGML Mozilla Calendar V1.0//EN\n"+
        "METHOD\n"+
        " :PUBLISH\n"+
        "BEGIN:VEVENT\n"+
        "UID\n"+
        " :788abf80-16aa-11d9-8d97-9159c000f28c\n"+
        "SUMMARY\n"+
        " :Mecka\n"+
        "LOCATION\n"+
        " :bengtsfors\n"+
        "STATUS\n"+
        " :TENTATIVE\n"+
        "CLASS\n"+
        " :PRIVATE\n"+
        "X-MOZILLA-RECUR-DEFAULT-INTERVAL\n"+
        " :0\n"+
        "DTSTART\n"+
        " :20041003T220000Z\n"+
        "DTEND\n"+
        " :20041003T230000Z\n"+
        "DTSTAMP\n"+
        " :20041005T084207Z\n"+
        "END:VEVENT\n"+
        "END:VCALENDAR\n"+
        "BEGIN:VCALENDAR\n"+
        "VERSION\n"+
        " :2.0\n"+
        "PRODID\n"+
        " :-//Mozilla.org/NONSGML Mozilla Calendar V1.0//EN\n"+
        "METHOD\n"+
        " :PUBLISH\n"+
        "BEGIN:VEVENT\n"+
        "UID\n"+
        " :22b50340-16aa-11d9-abc8-9456f9b7f112\n"+
        "SUMMARY\n"+
        " :Onsdag kl 12\n"+
        "LOCATION\n"+
        " :d‰r\n"+
        "STATUS\n"+
        " :TENTATIVE\n"+
        "CLASS\n"+
        " :PRIVATE\n"+
        "X-MOZILLA-RECUR-DEFAULT-INTERVAL\n"+
        " :0\n"+
        "DTSTART\n"+
        " :20041005T100000Z\n"+
        "DTEND\n"+
        " :20041005T120000Z\n"+
        "DTSTAMP\n"+
        " :20041005T083943Z\n"+
        "END:VEVENT\n"+
        "END:VCALENDAR\n"+
        "BEGIN:VCALENDAR\n"+
        "VERSION\n"+
        " :2.0\n"+
        "PRODID\n"+
        " :-//Mozilla.org/NONSGML Mozilla Calendar V1.0//EN\n"+
        "METHOD\n"+
        " :PUBLISH\n"+
        "BEGIN:VEVENT\n"+
        "UID\n"+
        " :48cc4730-f1e9-11d8-bb16-a36ba35c1c2a\n"+
        "SUMMARY\n"+
        " :Torsdag kl 16\n"+
        "LOCATION\n"+
        " :linkebo\n"+
        "STATUS\n"+
        " :TENTATIVE\n"+
        "CLASS\n"+
        " :PUBLIC\n"+
        "RRULE\n"+
        " :FREQ=WEEKLY;INTERVAL=1;BYDAY=TH\n"+
        "DTSTART\n"+
        " :20040819T140000Z\n"+
        "DTEND\n"+
        " :20040819T150000Z\n"+
        "DTSTAMP\n"+
        " :20040819T140831Z\n"+
        "LAST-MODIFIED\n"+
        " :20041005T083936Z\n"+
        "END:VEVENT\n"+
        "END:VCALENDAR\n";
}
