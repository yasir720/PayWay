/*
 * simple notifier class to abstract alert and confirm dialogs
 */

export class Notifier {
    static alert(message) { window.alert(message); }
    static confirm(message) { return window.confirm(message); }
}