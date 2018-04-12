<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\User;
use Twilio\Twiml;
use Illuminate\Http\Request;
use Twilio\Exceptions\TwimlException;

class TextController extends Controller
{
    private $user;
    private $twiml;
    private $message;
    private $callingNumber;

    /**
     * TextController constructor.
     * @param Twiml $twiml
     */
    public function __construct(Twiml $twiml)
    {
        try {
            $this->twiml = new $twiml();
        } catch (TwimlException $e) {
            \Log::error('TextController@entry: Could not create Twiml object', [$e->getMessage()]);
            return response('Could not create Twiml object', 500);
        }
    }


    /**
     * Entry point for Twilio webhook
     *
     * @param Request $request
     * @return mixed
     */
    public function entry(Request $request)
    {
        $this->callingNumber = $request->input('From');
        $this->message = $request->input('Body');
        \Log::info('TextController@entry: Incoming Text from: ', [$this->callingNumber, $this->message]);

        if ($this->user = User::where('phone_number', $this->callingNumber)->first()) {
            \Log::info('TextController@entry: Found the User based on calling party number: ', [$this->user->name]);
            if ($this->user->hasRole('admin')) {
                \Log::info('TextController@entry: This user is an admin.  Starting adminInterface ', [$this->user->name]);
                return $this->adminInterface();
            } else {
                \Log::info('TextController@entry: This user is a standard user.  Starting userInterface ', [$this->user->name]);
                return $this->userInterface();
            }
        } else {
            \Log::info('TextController@entry: Did not find the user in the database. Silence is golden. ', []);
        }
    }


    /**
     * Main place for interacting with Admin users
     *
     * @return mixed
     */
    private function adminInterface()
    {
        \Log::info('TextController@adminInterface: Checking SMS body', []);
        switch (true) {
            case stristr($this->message, 'add'):
                return $this->adminAddUser();
                break;
            case stristr($this->message, 'remove'):
                return $this->adminRemoveUser();
                break;
            case stristr($this->message, 'list'):
                return $this->adminListUsers();
                break;
            case stristr($this->message, 'search'):
                return $this->adminSearchUsers();
                break;
            case stristr($this->message, 'command'):
                return $this->adminListCommands();
                break;
            default:
                \Log::info('TextController@adminInterface: No pre-canned messages found.  Sending generic instructions.', []);
                $message = $this->twiml->message();
                $defaultMessage = "Hello, and welcome to TextMyTimeSheet!\n\n" .
                    "Start by using a command to manage your team.\n\n" .
                    "For a list of commands, text 'commands'\n\n";

                $message->body($defaultMessage);
                break;
        }
        return response($this->twiml)->header('Content-Type', 'application/xml');

    }


    /**
     * Where Admins can add new app Users or get help
     *
     * @return mixed
     */
    private function adminAddUser()
    {
        \Log::info('TextController@adminAddUser: SMS body contains the "add" keyword', []);

        if (stristr($this->message, 'help')) {
            \Log::info('TextController@adminAddUser: SMS body also contains the "help" keyword.  This is a request for help.', []);
            $adminHelp = "You can add a user by texting:\n\n" .
                "add <username> @ <phoneNumber>\n\n" .
                "For example:\n\n" .
                "add john smith @ 7035551234";
            $message = $this->twiml->message();
            $message->body($adminHelp);
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminAddUser: Parsing body text to add new user', []);
        preg_match("/^add(.*)@(.*)$/i", $this->message, $matches);
        $newUserName = trim($matches[1]);
        $newPhoneNumber = '+1' . substr(trim($matches[2]), -10);
        $email = strtolower(str_replace(' ', '', $newUserName)) . '@textmytimesheet.com';

        \Log::info('TextController@adminAddUser: Extracted User and PhoneNumber and generated dummy email: ', [$newUserName, $newPhoneNumber]);

        try {
            $user = User::firstOrCreate([
                'name' => $newUserName,
                'email' => $email,
                'phone_number' => $newPhoneNumber,
                'password' => bcrypt('p@$$word!')
            ])->assignRole('user');
        } catch (\Exception $e) {
            \Log::error('TextController@adminAddUser: Add User error: ', [$newUserName, $newPhoneNumber, $email, $e->getMessage()]);
            $message = $this->twiml->message();
            $message->body($e->getMessage());
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminAddUser: New user created successfully!', [$user->name]);
        $message = $this->twiml->message();
        $message->body(sprintf("Congrats!  New user %s has been added.  We'll send them a text message so they can get started", $user->name));
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }


    /**
     * Where Admins can remove app Users or get help
     * @return mixed
     */
    private function adminRemoveUser()
    {
        \Log::info('TextController@adminRemoveUser: SMS body contains the "remove" keyword', []);

        if (stristr($this->message, 'help')) {
            \Log::info('TextController@adminRemoveUser: SMS body also contains the "help" keyword.  This is a request for help.', []);
            $adminHelp = "You can remove a user by texting:\n\n" .
                "remove <username>\n\n" .
                "For example:\n\n" .
                "remove john smith\n\n" .
                "Their existing time entries won't be removed.";
            $message = $this->twiml->message();
            $message->body($adminHelp);
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminRemoveUser: Parsing body text to remove new user', []);
        preg_match("/^remove(.*)$/i", $this->message, $matches);
        $removeUserName = trim($matches[1]);

        \Log::info('TextController@adminRemoveUser: Extracted User: ', [$removeUserName]);

        if ($user = User::where('name', $removeUserName)->first()) {
            try {
                $user->delete();
            } catch (\Exception $e) {
                $message = $this->twiml->message();
                $message->body($e->getMessage());
                return response($this->twiml)->header('Content-Type', 'application/xml');
            }
        } else {
            \Log::info('TextController@adminRemoveUser: User removed successfully!', [$removeUserName]);
            $message = $this->twiml->message();
            $message->body(sprintf("Sorry, we couldn't find the user '%s'.  Text 'list' to list all of you current users.", $removeUserName));
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminRemoveUser: User removed successfully!', [$removeUserName]);
        $message = $this->twiml->message();
        $message->body(sprintf("Congrats!  User %s has been removed.  They can no longer add time via TextMyTime", $removeUserName));
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }


    /**
     * Where Admins can list app Users
     *
     * @return mixed
     */
    private function adminListUsers()
    {
        \Log::info('TextController@adminListUsers: SMS body contains the "list" keyword', []);

        if (stristr($this->message, 'help')) {
            \Log::info('TextController@adminListUsers: SMS body also contains the "help" keyword.  This is a request for help.', []);
            $adminHelp = "The 'list' command provides you a list" .
                         " of the user names configured for your TextMyTime app.";
            $message = $this->twiml->message();
            $message->body($adminHelp);
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminListUsers: Getting a list of user names to return.', []);
        $users = implode("\n", \App\User::role('user')->pluck('name')->toArray());

        if( ! $users) {
            \Log::info('TextController@adminListUsers: No users found to list', [$users]);
            $message = $this->twiml->message();
            $message->body("Sorry, we didn't find any users to list.  " .
                           "If you think this is an error, you might want to check with the TMTS admin\n\n ¯\_(ツ)_/¯");
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminListUsers: Obtained a list of users', [$users]);
        $message = $this->twiml->message();
        $message->body(sprintf("Here's your list of users:\n%s", $users));
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }

    /**
     * Provide a list of TextMyTimesheet Admin commands
     *
     * @return mixed
     */
    private function adminListCommands()
    {
        \Log::info('TextController@adminListCommands: SMS body contains the "command" keyword.  Returning a list of commands.', []);
        $adminHelp = "Command list:\n\n" .
            "add\n" .
            "remove\n" .
            "list\n" .
            "search\n\n" .
            "For help with any command, just text <command> help.\n\n" .
            "For example: 'add help'";
        $message = $this->twiml->message();
        $message->body($adminHelp);
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }


    /**
     * Where Admins can search Users
     *
     * @return mixed
     */
    private function adminSearchUsers()
    {
        \Log::info('TextController@adminSearchUsers: SMS body contains the "search" keyword', []);

        if (stristr($this->message, 'help')) {
            \Log::info('TextController@adminSearchUsers: SMS body also contains the "help" keyword.  This is a request for help.', []);
            $adminHelp = "The 'search' command provides you a list" .
                " of user names based on a query string.\n\n" .
                "For example:\n\n" .
                "search john\n\n" .
                "Will return John Smith and Jimmy Johns!";
            $message = $this->twiml->message();
            $message->body($adminHelp);
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminSearchUsers: Extracting query string from SMS', []);
        preg_match("/^search\s(.*)$/i", $this->message, $matches);
        $queryString = '%' . trim($matches[1]) . '%';

        \Log::info('TextController@adminSearchUsers: Getting a list of user names based on a query string.', []);
        $users = implode("\n", \App\User::role('user')->where('name', 'like', $queryString)->pluck('name')->toArray());

        if( ! $users) {
            \Log::info('TextController@adminSearchUsers: No users found for search terms', [$users]);
            $message = $this->twiml->message();
            $message->body("Sorry, we didn't find any users based on that search.  " .
                           "If you think this is in error, you might want to check with the TMTS admin\n\n ¯\_(ツ)_/¯");
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@adminSearchUsers: Obtained a list of users', [$users]);
        $message = $this->twiml->message();
        $message->body(sprintf("Here's your list of users:\n%s", $users));
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }


    /**
     * Main interface for User interactions
     *
     * @return mixed
     */
    private function userInterface()
    {
        \Log::info('TextController@userInterface: Checking SMS body', []);
        switch (true) {
            case stristr($this->message, 'add'):
                return $this->userAddTimeEntry();
                break;
            case stristr($this->message, 'list'):
                return $this->userListTimeEntries();
                break;
            case stristr($this->message, 'commands'):
                return $this->userListCommands();
                break;
            default:
                $message = $this->twiml->message();
                $defaultMessage = "Hello, and welcome to TextMyTimeSheet!\n\n" .
                    "Start by using a command to manage your timesheet.\n\n" .
                    "For a list of commands, text 'commands'\n\n";


                $message->body($defaultMessage);
                break;
        }
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }

    /**
     * Where Users can add Time Entries
     *
     * @return mixed
     */
    private function userAddTimeEntry()
    {
        \Log::info('TextController@userAddTimeEntry: SMS body contains the "add" keyword', []);

        if (stristr($this->message, 'help')) {
            \Log::info('TextController@userAddTimeEntry: SMS body also contains the "help" keyword.  This is a request for help.', []);
            $adminHelp = "You can add an entry to your timesheet by texting:\n\n" .
                "add <time> for <project>\n\n" .
                "For example:\n\n" .
                "add 8 for Miller's deck";
            $message = $this->twiml->message();
            $message->body($adminHelp);
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@userAddTimeEntry: Parsing body text to add new time entry', []);
        preg_match("/^add(.*)for(.*)$/i", $this->message, $matches);
        $hours = trim($matches[1]);
        $project = trim($matches[2]);

        \Log::info('TextController@userAddTimeEntry: Extracted time and project: ', []);

        try {
            $user = TimeEntry::firstOrCreate([
                'project' => $project,
                'hours' => $hours,
                'user_id' => $this->user->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('TextController@userAddTimeEntry: Add User error: ', [$project, $hours, $this->user->name, $e->getMessage()]);
            $message = $this->twiml->message();
            $message->body($e->getMessage());
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@userAddTimeEntry: New time entry created successfully!', [$this->user->name]);
        $message = $this->twiml->message();
        $message->body(sprintf("Congrats!  The %s time entry has been added as %s hours.", $project, $hours));
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }

    /**
     * Provide a list of TextMyTimesheet Admin commands
     *
     * @return mixed
     */
    private function userListCommands()
    {
        \Log::info('TextController@adminListCommands: SMS body contains the "command" keyword.  Returning a list of commands.', []);
        $adminHelp = "Command list:\n\n" .
            "add\n" .
            "remove\n" .
            "list\n" .
            "search\n\n" .
            "For help with any command, just text <command> help.\n\n" .
            "For example: 'add help'";
        $message = $this->twiml->message();
        $message->body($adminHelp);
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }

    /**
     * Where Admins can list app Users
     *
     * @return mixed
     */
    private function userListTimeEntries()
    {
        \Log::info('TextController@userListTimeEntries: SMS body contains the "list" keyword', []);

        if (stristr($this->message, 'help')) {
            \Log::info('TextController@userListTimeEntries: SMS body also contains the "help" keyword.  This is a request for help.', []);
            $adminHelp = "The 'list' command provides you a list" .
                " of your time entries for the past week.  Just type 'list' and we'll send them back!";
            $message = $this->twiml->message();
            $message->body($adminHelp);
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        \Log::info('TextController@userListTimeEntries: Getting a list of time entries for the past week.', []);

        $timeEntries = $this->user->timeEntries;

        if( ! $timeEntries) {
            \Log::info('TextController@userListTimeEntries: No time entries found to list', [$timeEntries]);
            $message = $this->twiml->message();
            $message->body("Sorry, we didn't find any time entries to list.  " .
                "If you think this is an error, you might want to check with the TMTS admin\n\n ¯\_(ツ)_/¯");
            return response($this->twiml)->header('Content-Type', 'application/xml');
        }

        $listing = '';
        foreach ($timeEntries as $time) {
            $listing .= "$time->project ({$time->hours}hrs) on {$time->created_at->toFormattedDateString()}\n";
        }

        \Log::info('TextController@adminListUsers: Obtained a list of time entries', [$timeEntries]);
        $message = $this->twiml->message();
        $message->body(sprintf("Here's your list of time entries:\n\n%s", $listing));
        return response($this->twiml)->header('Content-Type', 'application/xml');
    }
}
