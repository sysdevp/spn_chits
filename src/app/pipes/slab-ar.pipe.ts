import { Pipe, PipeTransform } from '@angular/core';
import { enrolNameWithAr, slabLabelFromRow } from '../utils/enrollment-display.util';

@Pipe({ name: 'slabAr' })
export class SlabArPipe implements PipeTransform {
  /**
   * Usage:
   *   {{ row | slabAr }}           → slab / ar_no
   *   {{ row | slabAr:'enrol' }}   → enrol_name with AR NO
   */
  transform(row: any, mode?: string): string {
    if (mode === 'enrol') {
      return enrolNameWithAr(row);
    }
    return slabLabelFromRow(row);
  }
}
