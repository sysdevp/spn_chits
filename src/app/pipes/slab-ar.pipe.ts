import { Pipe, PipeTransform } from '@angular/core';
import { enrolNameWithAr, isPrizedRow, slabLabelFromRow, withPrizedTag } from '../utils/enrollment-display.util';

@Pipe({ name: 'slabAr' })
export class SlabArPipe implements PipeTransform {
  /**
   * Usage:
   *   {{ row | slabAr }}           → slab / ar_no
   *   {{ row | slabAr:'enrol' }}   → enrol_name with AR NO
   * Prized enrollments get the "SPN Prized" tag appended.
   */
  transform(row: any, mode?: string): string {
    const label = mode === 'enrol' ? enrolNameWithAr(row) : slabLabelFromRow(row);
    return isPrizedRow(row) ? withPrizedTag(label) : label;
  }
}
