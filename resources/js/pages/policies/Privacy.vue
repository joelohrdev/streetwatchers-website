<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PolicyPage from '@/components/marketing/PolicyPage.vue';
import { codeOfConduct } from '@/routes';
import { create as contact } from '@/routes/contact-messages';
import { edit as profileSettings } from '@/routes/profile';
</script>

<template>
    <PolicyPage title="Privacy policy" updated="September 22, 2026">
        <p>
            StreetWatchers is a place for street photographers to find local
            groups and meet up for photo walks. This policy explains what
            information we collect, who can see it, and the choices you have.
        </p>
        <p>
            StreetWatchers is run by an individual, referred to as "we" and "us"
            in this policy. We're responsible for the information you share on
            the site.
        </p>

        <h2>What we collect</h2>
        <ul>
            <li>
                <strong>Your account:</strong> your name, email address and
                password. Passwords are stored in a scrambled (hashed) form, so
                we can't read them.
            </li>
            <li>
                <strong>Your profile:</strong> your Instagram username, if you
                choose to add one.
            </li>
            <li>
                <strong>Your activity:</strong> the groups you join or propose,
                whether you organize a group, the meetups you plan, and the
                meetups you RSVP to.
            </li>
            <li>
                <strong>Groups you propose:</strong> the group's name,
                description, city, country and map location.
            </li>
            <li>
                <strong>Messages you send us:</strong> what you write in the
                contact form or a photo removal request, with the name and email
                address you give.
            </li>
            <li>
                <strong>Technical information:</strong> while you're signed in,
                we store your session with your IP address and browser type. Our
                servers also keep short-term logs of requests to help us fix
                problems and prevent abuse.
            </li>
        </ul>

        <h2>Who can see what</h2>
        <ul>
            <li>
                <strong>Anyone, including visitors without an account:</strong>
                group pages (name, description, location and member count) and
                meetup pages (details and how many people are going). The names
                of group organizers, and their Instagram usernames if they've
                added them, appear on their group's pages and the meetups they
                plan.
            </li>
            <li>
                <strong>Organizers of a group you join:</strong> your name and
                when you joined, and your name if you RSVP to one of their
                meetups.
            </li>
            <li>
                <strong>Site admins:</strong> account and group details, when
                they need them to review groups, handle reports and keep the
                site safe.
            </li>
        </ul>
        <p>
            Your email address is never shown to other members. Members who
            don't organize a group aren't named on any public page.
        </p>

        <h2>Your location</h2>
        <p>
            When you look for groups near you, we ask your browser for your
            location. Distances are worked out on your own device, and your
            location is never sent to us.
        </p>
        <p>
            When you propose a group, you can fill in its map location from your
            current position. Because that becomes the group's public map
            location, it's rounded to within about half a mile. You can also
            type a location in yourself.
        </p>

        <h2>How we use your information</h2>
        <ul>
            <li>To run the site: accounts, groups, meetups and RSVPs.</li>
            <li>
                To send emails you need, such as verifying your address,
                resetting your password, and updates about groups you propose or
                organize.
            </li>
            <li>To review new groups and keep members safe.</li>
            <li>To answer messages you send us.</li>
        </ul>
        <p>
            We don't sell your information or show ads. If you allow it, we use
            Google Analytics to see how the site is used, as described below.
        </p>

        <h2>Services we rely on</h2>
        <p>
            A hosting provider runs our servers and database, and an email
            provider delivers our emails. They handle your information only to
            provide those services to us.
        </p>
        <p>
            Maps on the site are drawn from map images loaded directly from a
            map provider. When your browser loads them, the provider receives
            your IP address. Our fonts come from our own site.
        </p>
        <p>
            If you allow analytics, we use Google Analytics. It records which
            pages you visit, how you arrived, your device and browser, and your
            approximate location, such as your country and city. Google handles
            this data under its own privacy terms and doesn't store your IP
            address. We use it only to understand how the site is used, never
            for ads. If you don't allow it, Google Analytics doesn't load at
            all.
        </p>

        <h2>Cookies</h2>
        <p>
            The site needs a few cookies to work: one that keeps you signed in,
            one that protects forms from forgery, a "remember me" cookie if you
            tick that box when you log in, and, for site admins, one that
            remembers whether the admin menu is open.
        </p>
        <p>
            If you allow analytics, Google Analytics also sets its own cookies,
            whose names start with <code>_ga</code>, to recognize return visits.
            We ask before setting them and remember your answer in your browser.
            You can change it at any time from "Cookie settings" at the bottom
            of every page. There are no advertising cookies.
        </p>

        <h2>How long we keep it</h2>
        <p>
            We keep your account information until you delete your account.
            Deleting it removes your account, your group memberships, your RSVPs
            and the meetups you planned. We keep messages you send us until
            we've dealt with your request, and server logs for a short time. We
            keep records of moderation decisions so they can be reviewed later.
        </p>

        <h2>Your rights</h2>
        <p>Wherever you live, you can:</p>
        <ul>
            <li>
                see and correct your name, email and Instagram username in
                <Link :href="profileSettings()">your profile settings</Link>;
            </li>
            <li>delete your account from the same page;</li>
            <li>
                ask us for a copy of the information we hold about you, or to
                correct, delete or stop using it;
            </li>
            <li>
                object to how we use your information, or ask us to limit it.
            </li>
        </ul>
        <p>
            To make a request, use our
            <Link :href="contact()">contact form</Link>. We'll respond within
            one month. If you're in the European Union or the United Kingdom and
            you're not happy with our response, you can complain to your local
            data protection authority.
        </p>

        <h2>Where your information is stored</h2>
        <p>
            StreetWatchers is used around the world, so your information may be
            stored and processed in a country other than yours.
        </p>

        <h2>Age</h2>
        <p>
            You must be 18 or older to create an account, because members meet
            each other in person.
        </p>

        <h2>Changes to this policy</h2>
        <p>
            If we change this policy, we'll update the date at the top. If a
            change significantly affects how we use your information, we'll also
            email you before it takes effect.
        </p>

        <h2>Questions</h2>
        <p>
            Get in touch through our
            <Link :href="contact()">contact form</Link>. Our
            <Link :href="codeOfConduct()">code of conduct</Link> explains how
            members are expected to treat each other and the people they
            photograph.
        </p>
    </PolicyPage>
</template>
