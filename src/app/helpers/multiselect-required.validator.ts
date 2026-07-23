import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

/** Required validator for ng-multiselect-dropdown (value is an array of { item_id, item_text }). */
export function multiselectRequired(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const value = control.value;
    if (!value || !Array.isArray(value) || value.length === 0) {
      return { required: true };
    }
    return null;
  };
}
