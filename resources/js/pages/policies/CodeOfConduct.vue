<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PolicyPage from '@/components/marketing/PolicyPage.vue';
import { create as contact } from '@/routes/contact-messages';
</script>

<template>
    <PolicyPage title="Code of conduct" updated="September 22, 2026">
        <p>
            StreetWatchers brings photographers together to walk and shoot in
            public. That only works if everyone feels safe and respected: the
            people in the group, and the people in front of the camera. This
            code applies on the site and at every StreetWatchers meetup.
        </p>

        <h2>Respect the people you photograph</h2>
        <ul>
            <li>
                Know and follow the local law on photographing people in public.
                It varies from country to country.
            </li>
            <li>
                If someone asks you not to photograph them, or to delete a photo
                of them, do it without argument.
            </li>
            <li>
                Don't follow, crowd, block or pressure anyone to get a shot.
            </li>
            <li>
                Take extra care around children, and around people who are
                vulnerable, in distress or receiving care. Don't take photos
                that mock or humiliate anyone.
            </li>
        </ul>

        <h2>Respect each other</h2>
        <ul>
            <li>
                Welcome everyone, whatever their experience, gear or background.
            </li>
            <li>
                No harassment, discrimination, intimidation, or unwanted
                physical contact or attention.
            </li>
            <li>
                Ask fellow members before you photograph them, and before you
                share photos of them.
            </li>
        </ul>

        <h2>Stay safe at meetups</h2>
        <ul>
            <li>Meet in public places, and tell someone where you're going.</li>
            <li>
                Keep an eye on traffic and your surroundings while you're
                shooting, and look out for each other.
            </li>
            <li>You're free to leave a meetup at any time, for any reason.</li>
        </ul>

        <h2>If you organize a group</h2>
        <ul>
            <li>
                Plan meetups in public places, with a clear meeting point and
                accurate times.
            </li>
            <li>
                If plans change, update or cancel the meetup on the site so
                nobody is left waiting.
            </li>
            <li>
                Use your members' names and RSVPs only to run the group. Don't
                share them outside it.
            </li>
            <li>
                Deal with problems at your meetups, and tell us about anything
                serious.
            </li>
        </ul>

        <h2>On the site</h2>
        <ul>
            <li>Be honest about who you are.</li>
            <li>No spam, advertising or selling to members.</li>
            <li>
                Don't use contact details you find through StreetWatchers to
                contact members who don't want to hear from you.
            </li>
        </ul>

        <h2>Reporting a problem</h2>
        <p>
            If someone breaks this code, or you feel unsafe, tell us through our
            <Link :href="contact()">contact form</Link>. We'll look into every
            report and keep your name confidential. If you or someone else is in
            immediate danger, contact your local emergency services first.
        </p>

        <h2>What happens if the code is broken</h2>
        <p>
            Depending on what happened, we may give a warning, remove someone
            from a group or from organizing it, suspend or close their account,
            or deactivate a group. We decide case by case, and put the safety of
            members and the public first.
        </p>
    </PolicyPage>
</template>
